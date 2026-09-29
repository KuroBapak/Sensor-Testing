#!/usr/bin/env python3
"""
J1939 logger (host side) untuk firmware j1939_logger.ino v2.

  python3 log_to_csv.py --list                       daftar port serial (STM32 CDC = 0483:5740)
  python3 log_to_csv.py /dev/ttyACM0 --check         tes komunikasi 10 detik + diagnosa (tanpa file)
  python3 log_to_csv.py /dev/ttyACM0                 rekam (folder sesi baru: logs/session_<timestamp>/)
  python3 log_to_csv.py /dev/ttyACM0 -n truk1        folder tetap logs/truk1 (kalau sudah ada -> ditanya hapus)
  python3 log_to_csv.py /dev/ttyACM0 -n truk1 --overwrite   hapus file lama tanpa tanya
  python3 log_to_csv.py /dev/ttyACM0 --monitor       tampilkan semua baris mentah dari MCU

Isi folder sesi:
  raw.csv       semua frame CAN (std + ext)
  decoded.csv   sinyal J1939 hasil decode (format long: signal,value)
  status.csv    status tiap detik dari MCU (rx_total, ring_drop, hw_ovr, rec/tec, ...)
  events.log    boot, gap, disconnect, warning, pesan I/W/E dari firmware
  bad_lines.log baris rusak (CRC/format salah) apa adanya
  session.json  metadata + counter (di-update tiap 5 detik)

Jaminan: tiap baris ber-seq + CRC8. Baris hilang/rusak TIDAK diam-diam: tercatat di events.log,
bad_lines.log dan ditampilkan di console. Data di-flush tiap 1 s dan fsync tiap 5 s.
"""
import argparse
import collections
import csv
import json
import os
import queue
import re
import shutil
import socket
import sys
import threading
import time
from datetime import datetime

try:
    import serial
    from serial.tools import list_ports
except ImportError:
    sys.exit("pyserial belum ada.  sudo apt install python3-serial   atau di venv: pip install pyserial")

M32 = 0xFFFFFFFF
KNOWN_FILES = ["raw.csv", "decoded.csv", "status.csv", "events.log", "bad_lines.log",
               "session.json", "session.json.tmp"]
RAW_HDR = ["pc_time", "boot", "seq", "t_us", "ext", "can_id_hex", "pgn", "sa", "priority", "dlc", "data_hex"]
DEC_HDR = ["pc_time", "boot", "seq", "t_us", "sa", "signal", "value"]
ST_HDR = ["pc_time", "boot", "seq", "t_us", "rx_total", "fps", "ring_drop", "ring_hwm", "hw_ovr", "eflg",
          "rec", "tec", "tx_hwm", "loop_max_us", "silent_ms", "line_drop", "opmod"]
PGN_NAMES = {
    61444: "EEC1", 61443: "EEC2", 65265: "CCVS", 65262: "ET1", 65263: "EFL/P1", 65266: "LFE",
    65270: "IC1", 65271: "VEP1", 65276: "DD", 65253: "HOURS", 65248: "VD", 65226: "DM1",
    60416: "TP.CM", 60160: "TP.DT", 59904: "REQUEST", 60928: "ADDR_CLAIM", 61442: "ETC1",
    65217: "VDHR", 65242: "SOFT", 65259: "CI", 61441: "EBC1", 65269: "AMB", 65272: "TRF1",
    65213: "FD", 65247: "EEC3",
}


def crc8(data: bytes) -> int:
    """CRC-8 poly 0x07 init 0 -- identik dengan crc8() di firmware."""
    crc = 0
    for b in data:
        crc ^= b
        for _ in range(8):
            crc = ((crc << 1) ^ 0x07) & 0xFF if crc & 0x80 else (crc << 1) & 0xFF
    return crc


def iso(ts: float) -> str:
    return datetime.fromtimestamp(ts).isoformat(timespec="milliseconds")


# ----------------------------------------------------------------------------- recorder
class Recorder:
    """Penulis file. folder=None -> mode tanpa file (untuk --check)."""

    def __init__(self, folder):
        self.folder = folder
        self.enabled = folder is not None
        self.files = []
        if not self.enabled:
            return

        def opencsv(name, hdr):
            f = open(os.path.join(folder, name), "x", newline="", buffering=65536)  # "x": tidak pernah menimpa
            self.files.append(f)
            w = csv.writer(f, lineterminator="\n")
            w.writerow(hdr)
            return w

        def opentxt(name):
            f = open(os.path.join(folder, name), "x", buffering=1)
            self.files.append(f)
            return f

        self.w_raw = opencsv("raw.csv", RAW_HDR)
        self.w_dec = opencsv("decoded.csv", DEC_HDR)
        self.w_st = opencsv("status.csv", ST_HDR)
        self.f_ev = opentxt("events.log")
        self.f_bad = opentxt("bad_lines.log")

    def raw(self, row):
        if self.enabled: self.w_raw.writerow(row)

    def dec(self, row):
        if self.enabled: self.w_dec.writerow(row)

    def status(self, row):
        if self.enabled: self.w_st.writerow(row)

    def event(self, ts, level, msg):
        if self.enabled: self.f_ev.write(f"{iso(ts)}\t{level}\t{msg}\n")

    def bad(self, ts, kind, text):
        if self.enabled: self.f_bad.write(f"{iso(ts)}\t{kind}\t{text}\n")

    def flush(self, sync=False):
        for f in self.files:
            f.flush()
            if sync:
                os.fsync(f.fileno())

    def close(self):
        if not self.enabled: return
        self.flush(sync=True)
        for f in self.files:
            f.close()


# ----------------------------------------------------------------------------- sesi
class Session:
    def __init__(self, rec, monitor=False, meta=None):
        self.rec, self.monitor, self.meta = rec, monitor, meta or {}
        self.buf, self.sample = bytearray(), bytearray()
        self.c = collections.Counter()
        self.pgns = collections.Counter()
        self.latest = {}
        self.boot, self.expected = 0, None
        self.st, self.st_prev = None, None
        self.total_bytes, self.last_byte_ts = 0, None
        self.connected, self.t_connect = False, None
        self.fw_msgs = []
        self.t0 = time.time()
        self.last_console = self.last_flush = self.last_sync = self.t0
        self.last_raw_cnt = 0
        self.last_idle_warn = self.last_garbage_warn = 0.0
        self.tty = sys.stdout.isatty()

    # ---- output helpers
    def say(self, msg):
        if self.tty:
            sys.stdout.write("\r" + " " * 150 + "\r")
        print(msg, flush=True)

    def event(self, ts, level, msg, show=True):
        self.rec.event(ts, level, f"boot={self.boot} {msg}")
        if show and level != "INFO":
            self.say(f"[{level}] {msg}")

    def bad(self, ts, kind, text):
        self.c["bad"] += 1
        self.rec.bad(ts, kind, text)
        if self.c["bad"] <= 5 or self.c["bad"] % 100 == 0:
            self.say(f"[BAD] baris {kind} (total {self.c['bad']}): {text[:80]!r}")

    # ---- item dari reader thread
    def on_item(self, item):
        kind, ts, payload = item
        if kind == "data":
            self.feed(ts, payload)
        elif kind == "connected":
            self.connected, self.t_connect, self.last_byte_ts = True, ts, ts
            self.event(ts, "INFO", f"serial terhubung: {payload}")
            self.say(f"[OK] serial terbuka: {payload}")
        elif kind == "disconnected":
            self.connected = False
            self.buf.clear()
            self.event(ts, "ERROR", f"serial putus: {payload} (auto-reconnect...)")
        elif kind == "waiting":
            self.say(f"[..] menunggu port: {payload}")

    def feed(self, ts, data):
        self.total_bytes += len(data)
        self.last_byte_ts = ts
        if len(self.sample) < 80:
            self.sample += data[: 80 - len(self.sample)]
        self.buf += data
        while True:
            i = self.buf.find(b"\n")
            if i < 0:
                break
            line = bytes(self.buf[:i])
            del self.buf[: i + 1]
            self.handle_line(line, ts)
        if len(self.buf) > 4096:
            self.bad(ts, "OVERLONG", repr(bytes(self.buf[:80])))
            self.buf.clear()

    # ---- parsing satu baris
    def handle_line(self, raw, ts):
        text = raw.decode("ascii", errors="replace").rstrip("\r")
        if not text.strip():
            return
        if self.monitor:
            self.say(text)
        star = text.rfind("*")
        if star < 1 or len(text) - star - 1 != 2:
            return self.bad(ts, "FORMAT", text)
        payload, ck = text[:star], text[star + 1:]
        try:
            ok = int(ck, 16) == crc8(payload.encode("ascii", errors="replace"))
        except ValueError:
            ok = False
        if not ok:
            return self.bad(ts, "CRC", text)

        typ = payload[:1]
        if typ in "IWE":
            f = payload.split(",", 3)
            need = 4
        else:
            f = payload.split(",")
            need = {"R": 10, "D": 6, "S": 16}.get(typ, -1)
        if len(f) != need:
            return self.bad(ts, "FIELDS", text)
        try:
            seq, t_us = int(f[1]), int(f[2])
        except ValueError:
            return self.bad(ts, "FORMAT", text)

        self.c["valid"] += 1
        self.check_seq(ts, typ, seq, f[3] if typ in "IWE" else "")
        p = iso(ts)

        if typ == "R":
            self.c["raw"] += 1
            self.rec.raw([p, self.boot, seq, t_us] + f[3:])
            if f[3] == "1":
                self.pgns[(int(f[5]), int(f[6]))] += 1
        elif typ == "D":
            self.c["dec"] += 1
            self.rec.dec([p, self.boot, seq, t_us] + f[3:])
            self.latest[f[4]] = f[5]
        elif typ == "S":
            self.c["status"] += 1
            self.rec.status([p, self.boot, seq, t_us] + f[3:])
            self.on_status(ts, f)
        else:  # I / W / E dari firmware
            self.fw_msgs.append((typ, f[3]))
            self.rec.event(ts, f"FW-{typ}", f"boot={self.boot} seq={seq} t_us={t_us} {f[3]}")
            self.say(f"[FW-{typ}] {f[3]}")

    def check_seq(self, ts, typ, seq, text):
        if typ == "I" and text.startswith("BOOT"):
            self.boot += 1
            self.expected = (seq + 1) & M32
            self.event(ts, "INFO", f"MCU BOOT (boot#{self.boot})")
            return
        if self.boot == 0:
            self.boot = 1
        if self.expected is not None:
            diff = (seq - self.expected) & M32
            if diff and diff < 0x80000000:
                self.c["gaps"] += 1
                self.c["lost_lines"] += diff
                self.event(ts, "WARN", f"GAP seq: expected={self.expected} got={seq} -> {diff} baris hilang")
            elif diff:
                self.boot += 1
                self.event(ts, "WARN", f"seq mundur (expected={self.expected} got={seq}) -> MCU reset? boot#{self.boot}")
        self.expected = (seq + 1) & M32

    def on_status(self, ts, f):
        keys = ["rx_total", "fps", "ring_drop", "ring_hwm", "hw_ovr", "eflg", "rec", "tec",
                "tx_hwm", "loop_max_us", "silent_ms", "line_drop", "opmod"]
        vals = f[3:]
        st = dict(zip(keys, vals))
        for k in keys:
            st[k] = int(st[k], 16) if k == "eflg" else int(st[k])
        st["boot"] = self.boot
        prev = self.st if (self.st and self.st["boot"] == self.boot) else None
        if prev:
            for k, what in (("ring_drop", "frame-ring penuh"), ("hw_ovr", "MCP2515 RX overflow")):
                if st[k] > prev[k]:
                    self.event(ts, "ERROR", f"DATA LOSS di MCU: {what} (+{st[k] - prev[k]}, total {st[k]})")
        self.st = st

    # ---- periodik
    def tick(self, now):
        if now - self.last_flush >= 1.0:
            self.rec.flush()
            self.last_flush = now
        if now - self.last_sync >= 5.0:
            self.rec.flush(sync=True)
            self.write_meta()
            self.last_sync = now
        if self.connected:
            if self.last_byte_ts and now - self.last_byte_ts > 3 and now - self.last_idle_warn > 5:
                self.last_idle_warn = now
                self.say(NO_BYTES_HELP.format(n=now - self.last_byte_ts))
            if (self.total_bytes and not self.c["valid"] and now - self.t_connect > 5
                    and now - self.last_garbage_warn > 5):
                self.last_garbage_warn = now
                self.say(GARBAGE_HELP.format(s=bytes(self.sample[:60])))
        if now - self.last_console >= 1.0:
            dt = now - self.last_console
            rate = (self.c["raw"] - self.last_raw_cnt) / dt
            self.last_raw_cnt, self.last_console = self.c["raw"], now
            st = self.st or {}
            line = (f"[{int(now - self.t0) // 3600:02d}:{int(now - self.t0) // 60 % 60:02d}:{int(now - self.t0) % 60:02d}] "
                    f"raw={self.c['raw']} ({rate:.0f}/s) dec={self.c['dec']} gap={self.c['gaps']}/{self.c['lost_lines']} "
                    f"bad={self.c['bad']} | MCU silent={st.get('silent_ms', '-')}ms drop={st.get('ring_drop', '-')}/"
                    f"{st.get('hw_ovr', '-')} rec={st.get('rec', '-')} | rpm={self.latest.get('engine_rpm', '-')} "
                    f"spd={self.latest.get('vehicle_speed_kmh', '-')}")
            if self.tty:
                sys.stdout.write("\r" + line.ljust(150)[:150])
                sys.stdout.flush()
            elif int(now) % 5 == 0:
                print(line, flush=True)

    def write_meta(self, final=False):
        if not self.rec.enabled:
            return
        meta = dict(self.meta, host=socket.gethostname(), updated=iso(time.time()), final=final,
                    boots=self.boot, counters=dict(self.c), total_bytes=self.total_bytes, last_status=self.st)
        tmp = os.path.join(self.rec.folder, "session.json.tmp")
        with open(tmp, "w") as fh:
            json.dump(meta, fh, indent=2)
            fh.flush()
            os.fsync(fh.fileno())
        os.replace(tmp, os.path.join(self.rec.folder, "session.json"))

    # ---- ringkasan
    def summary(self):
        c = self.c
        out = ["", "=" * 60, "RINGKASAN",
               f"  byte diterima      : {self.total_bytes}",
               f"  baris valid        : {c['valid']}   (rusak/CRC: {c['bad']})",
               f"  frame CAN (raw)    : {c['raw']}",
               f"  sinyal decode      : {c['dec']}",
               f"  gap seq            : {c['gaps']} kejadian, {c['lost_lines']} baris hilang",
               f"  MCU reboot         : {max(self.boot - 1, 0)}"]
        if self.st:
            s = self.st
            out.append(f"  status MCU terakhir: rx_total={s['rx_total']} ring_drop={s['ring_drop']} hw_ovr={s['hw_ovr']} "
                       f"eflg=0x{s['eflg']:02X} rec={s['rec']} tec={s['tec']} loop_max={s['loop_max_us']}us opmod={s['opmod']}")
        if self.pgns:
            out.append("  PGN teratas (pgn, sa, jumlah):")
            for (pgn, sa), n in self.pgns.most_common(12):
                out.append(f"    {pgn:>6} {PGN_NAMES.get(pgn, ''):<10} sa={sa:<3} {n}")
        return "\n".join(out)

    def verdict(self):
        c = self.c
        if self.total_bytes == 0:
            return False, "TIDAK ADA BYTE dari MCU.\n" + NO_BYTES_HELP.format(n=0)
        if not c["valid"]:
            return False, "Ada byte tapi tidak ada baris valid.\n" + GARBAGE_HELP.format(s=bytes(self.sample[:60]))
        errs = [m for t, m in self.fw_msgs if t == "E"]
        if errs:
            return False, "MCU komunikasi OK tapi init CAN GAGAL:\n  - " + "\n  - ".join(dict.fromkeys(errs))
        if not c["raw"]:
            return False, ("Komunikasi MCU OK, chip MCP2515 OK, tapi TIDAK ADA FRAME CAN.\n"
                           "  Cek: kontak mobil ON | CAN-H/CAN-L tidak tertukar & memang bus J1939 | bitrate (coba 500k)\n"
                           "  | xtal modul 8/16 MHz | jumper terminasi 120 ohm | GND bersama\n"
                           "  Lihat rec/tec/eflg di ringkasan: kalau rec naik, curiga bitrate/xtal salah.")
        if c["bad"] or c["gaps"]:
            return False, f"Frame masuk tapi ada baris rusak ({c['bad']}) / gap ({c['gaps']}): cek kabel USB / baud."
        if self.st and (self.st["ring_drop"] or self.st["hw_ovr"]):
            return False, "Frame masuk tapi MCU melaporkan DATA LOSS (ring_drop/hw_ovr > 0)."
        if not c["dec"]:
            return True, ("Frame masuk, tanpa loss. Belum ada PGN engine yang ter-decode: lihat daftar PGN di atas "
                          "(mungkin proprietary / bukan bus J1939 utama).")
        return True, "SEMUA OK: komunikasi, self-test, frame CAN, decode, tanpa loss/gap."


NO_BYTES_HELP = """[!] Tidak ada byte dari MCU selama {n:.0f}s. Kemungkinan:
    1. Port salah        -> python3 log_to_csv.py --list   (STM32 CDC = VID:PID 0483:5740)
    2. Arduino IDE       -> Tools > USB support = "CDC (generic 'Serial' supersede U(S)ART)"; kalau tidak,
                            Serial = USART1 (PA9/PA10), bukan USB. Re-upload setelah ganti.
    3. Pakai USB-TTL     -> ganti LOG_SERIAL ke Serial1/USART, baud harus sama (921600), TX->RX silang, GND bersama
    4. Kabel USB charge-only / board belum ter-flash -> ganti kabel, upload ulang
    5. LED PC13          -> mati terus = firmware tidak jalan; kedip 5Hz = init gagal; blip 1/detik = hidup tapi bus sepi
    6. Permission        -> sudo usermod -aG dialout $USER  (lalu logout/login)"""
GARBAGE_HELP = """[!] Ada byte masuk tapi bukan protokol logger (contoh: {s!r}).
    Kemungkinan baud salah (harus sama dengan LOG_BAUD, 921600), firmware lama v1, atau port dipakai program lain."""


# ----------------------------------------------------------------------------- reader thread
class Reader(threading.Thread):
    def __init__(self, port, baud, q, stop):
        super().__init__(daemon=True)
        self.port, self.baud, self.q, self.stop = port, baud, q, stop

    def run(self):
        ser, last_err = None, None
        while not self.stop.is_set():
            if ser is None:
                try:
                    ser = serial.Serial(self.port, self.baud, timeout=0.1)
                    self.q.put(("connected", time.time(), self.port))
                    last_err = None
                except (serial.SerialException, OSError, ValueError) as e:
                    if str(e) != last_err:
                        self.q.put(("waiting", time.time(), str(e)))
                        last_err = str(e)
                    time.sleep(1.0)
                    continue
            try:
                data = ser.read(max(1, ser.in_waiting))
                if data:
                    self.q.put(("data", time.time(), data))
            except (serial.SerialException, OSError) as e:
                self.q.put(("disconnected", time.time(), str(e)))
                try:
                    ser.close()
                except Exception:
                    pass
                ser = None
                time.sleep(1.0)
        if ser is not None:
            ser.close()


# ----------------------------------------------------------------------------- main
def list_all_ports():
    ports = list(list_ports.comports())
    if not ports:
        print("Tidak ada port serial terdeteksi. (Linux: cek kabel USB data, `dmesg | tail`, grup dialout)")
    for p in ports:
        vp = f"{p.vid:04X}:{p.pid:04X}" if p.vid else "----:----"
        hint = "  <-- STM32 USB CDC" if vp == "0483:5740" else ""
        print(f"{p.device:<18} {vp}  {p.description}{hint}")


def prepare_folder(outdir, name, overwrite):
    os.makedirs(outdir, exist_ok=True)
    if name is None:
        name = datetime.now().strftime("session_%Y%m%d_%H%M%S")
    if not re.fullmatch(r"[A-Za-z0-9][A-Za-z0-9_.-]*", name):
        sys.exit("Nama sesi hanya boleh huruf/angka/_/-/. dan tidak boleh diawali titik.")
    folder = os.path.join(outdir, name)
    old = [f for f in KNOWN_FILES if os.path.exists(os.path.join(folder, f))]
    if old:
        if not overwrite:
            if not sys.stdin.isatty():
                sys.exit(f"'{folder}' sudah berisi data. Pakai --overwrite atau nama lain.")
            if input(f"'{folder}' sudah ada ({', '.join(old)}). Hapus file lama dulu? [y/N] ").strip().lower() != "y":
                sys.exit("Dibatalkan. Data lama tidak disentuh.")
        for f in old:
            os.remove(os.path.join(folder, f))
        print(f"[OK] {len(old)} file lama dihapus dari {folder}")
    os.makedirs(folder, exist_ok=True)
    free = shutil.disk_usage(folder).free
    if free < 1 << 30:
        print(f"[WARN] sisa disk hanya {free / 1e6:.0f} MB. Estimasi log ~100-300 MB/jam di bus padat.")
    return folder


def main():
    ap = argparse.ArgumentParser(description="J1939 logger host", formatter_class=argparse.RawDescriptionHelpFormatter,
                                 epilog=__doc__)
    ap.add_argument("port", nargs="?")
    ap.add_argument("-b", "--baud", type=int, default=921600)
    ap.add_argument("-o", "--outdir", default="logs")
    ap.add_argument("-n", "--name")
    ap.add_argument("--overwrite", action="store_true")
    ap.add_argument("--check", type=int, nargs="?", const=10, metavar="DETIK")
    ap.add_argument("--monitor", action="store_true")
    ap.add_argument("--list", action="store_true")
    args = ap.parse_args()

    if args.list:
        return list_all_ports()
    if not args.port:
        ap.error("port wajib (lihat: --list)")

    folder = None if args.check else prepare_folder(args.outdir, args.name, args.overwrite)
    rec = Recorder(folder)
    meta = dict(port=args.port, baud=args.baud, start=iso(time.time()), argv=sys.argv)
    sess = Session(rec, monitor=args.monitor, meta=meta)
    if folder:
        print(f"[OK] merekam ke {folder}/  (Ctrl+C untuk stop)")
    else:
        print(f"[OK] mode --check {args.check}s (tanpa file)")

    q, stop = queue.Queue(), threading.Event()
    Reader(args.port, args.baud, q, stop).start()
    deadline = time.time() + args.check if args.check else None
    try:
        while deadline is None or time.time() < deadline:
            try:
                sess.on_item(q.get(timeout=0.2))
            except queue.Empty:
                pass
            sess.tick(time.time())
    except KeyboardInterrupt:
        pass
    finally:
        stop.set()
        time.sleep(0.3)
        while not q.empty():
            sess.on_item(q.get_nowait())
        try:
            sess.write_meta(final=True)
        finally:
            rec.close()
        print()
        print(sess.summary())
        if args.check:
            ok, msg = sess.verdict()
            print("\n" + ("[PASS] " if ok else "[FAIL] ") + msg)
            sys.exit(0 if ok else 1)
        print(f"\nData tersimpan di: {folder}/")


if __name__ == "__main__":
    main()
