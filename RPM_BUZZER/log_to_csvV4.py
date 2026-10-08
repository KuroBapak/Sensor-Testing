#!/usr/bin/env python3
"""
J1939 logger (host side) untuk firmware j1939_logger.ino v3.0.   [versi + TP/DM1/ident + verdict EEC1]

  python3 log_to_csv.py --list                       daftar port serial (adaptor USB-TTL -> /dev/ttyUSB0, STM32 CDC -> /dev/ttyACM0)
  python3 log_to_csv.py /dev/ttyUSB0 --check         tes komunikasi 10 detik + diagnosa (tanpa file)
  python3 log_to_csv.py /dev/ttyUSB0                 rekam (folder sesi baru: logs/session_<timestamp>/)
  python3 log_to_csv.py /dev/ttyUSB0 -n truk1        folder tetap logs/truk1 (kalau sudah ada -> ditanya hapus)
  python3 log_to_csv.py /dev/ttyUSB0 -n truk1 --overwrite   hapus file lama tanpa tanya
  python3 log_to_csv.py /dev/ttyUSB0 --monitor       tampilkan semua baris mentah dari MCU
  python3 log_to_csv.py /dev/ttyUSB0 --bench 1       kirim "bench 1" ke firmware (NORMAL-ACK, HANYA meja test); --bench 0 = LISTEN-ONLY
                                                     (firmware selalu boot di bench 0; tidak merekam; port harus tidak dipakai proses lain)
  python3 log_to_csv.py --redecode logs/truk1        decode ulang raw.csv -> decoded_redecode.csv, dtc_redecode.csv, ident_redecode.csv

Baud default 921600 dan HARUS sama dengan LOG_BAUD di firmware.

Isi folder sesi:
  raw.csv       semua frame CAN (std + ext) apa adanya dari bus; pgn/sa/priority dihitung host dari CAN ID
  decoded.csv   sinyal J1939 hasil decode HOST (format long: signal,value). seq/t_us = milik frame sumbernya
  dtc.csv       DM1 (DTC aktif) / DM2 (DTC sebelumnya): HANYA saat isinya berubah (+ pertama kali terlihat per ECU).
                Mencakup DM1 satu-frame dan multi-paket (TP). seq/t_us = frame terakhir pesan itu.
  ident.csv     VIN, Component ID, Software ID (multi-paket). Muncul HANYA kalau ada alat lain di bus yang me-request
                (logger ini pasif, tidak pernah TX). Bisa saja kosong -> normal.
  status.csv    status tiap detik dari MCU (rx_total, ring_drop, hw_ovr, rec/tec, ...)
  events.log    boot, gap, disconnect, warning, pesan I/W/E dari firmware (termasuk hasil scan bitrate)
  bad_lines.log baris rusak (CRC/format salah) apa adanya
  session.json  metadata + counter (di-update tiap 5 detik)

Kejujuran data:
  - tiap baris ber-seq + CRC8. Baris rusak/hilang DI KABEL SERIAL tercatat (bad_lines.log, GAP di events.log, console).
  - Kehilangan DI MCU (ring penuh / overflow MCP2515) TIDAK muncul sebagai gap seq; itu dilaporkan lewat counter
    ring_drop / hw_ovr di status.csv + events.log ("DATA LOSS di MCU"). Sesi dianggap utuh hanya kalau keduanya 0.
  - Untuk analisis waktu pakai kolom t_us (jam MCU, per frame) + boot, BUKAN pc_time (satu chunk serial = satu stempel).
  - Data di-flush tiap 1 s dan fsync tiap 5 s.
  - Pesan multi-paket yang kehilangan satu frame DT (gap seq / overflow) tidak akan terrakit -> dihitung "timeout".
    raw.csv tetap utuh; rakit ulang offline dengan --redecode.
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
import subprocess
import sys
import threading
import time
from datetime import datetime
from decimal import Decimal, ROUND_HALF_UP

try:
    import serial
    from serial.tools import list_ports
except ImportError:
    sys.exit("pyserial belum ada.  sudo apt install python3-serial   atau di venv: pip install pyserial")

M32 = 0xFFFFFFFF
# Detik pertama setelah port dibuka: USB-serial (mis. ST-Link VCP) sering menyerahkan potongan data LAMA yang tertahan,
# lalu stream baru -> terlihat sebagai baris rusak + lompatan seq. Itu bukan kehilangan data di dalam sesi.
STARTUP_GRACE_S = 2.0
KNOWN_FILES = ["raw.csv", "decoded.csv", "dtc.csv", "ident.csv", "status.csv", "events.log", "bad_lines.log",
               "session.json", "session.json.tmp"]
RAW_HDR = ["pc_time", "boot", "seq", "t_us", "ext", "can_id_hex", "pgn", "sa", "priority", "dlc", "data_hex"]
DEC_HDR = ["pc_time", "boot", "seq", "t_us", "sa", "signal", "value"]
DTC_HDR = ["pc_time", "boot", "seq", "t_us", "sa", "msg", "lamp_mil", "lamp_red", "lamp_amber", "lamp_protect",
           "spn", "fmi", "occurrence", "cm"]
ID_HDR = ["pc_time", "boot", "seq", "t_us", "sa", "kind", "value"]
ST_HDR = ["pc_time", "boot", "seq", "t_us", "rx_total", "fps", "ring_drop", "ring_hwm", "hw_ovr", "eflg",
          "rec", "tec", "tx_hwm", "loop_max_us", "silent_ms", "line_drop", "opmod"]
PGN_NAMES = {
    61444: "EEC1", 61443: "EEC2", 65265: "CCVS", 65262: "ET1", 65263: "EFL/P1", 65266: "LFE",
    65270: "IC1", 65271: "VEP1", 65276: "DD", 65253: "HOURS", 65248: "VD", 65226: "DM1",
    65227: "DM2", 65260: "VI", 60416: "TP.CM", 60160: "TP.DT", 59904: "REQUEST", 60928: "ADDR_CLAIM",
    61442: "ETC1", 65217: "VDHR", 65242: "SOFT", 65259: "CI", 61441: "EBC1", 65269: "AMB", 65272: "TRF1",
    65213: "FD", 65247: "EEC3",
}
PGN_EEC1 = 61444

# ----------------------------------------------------------------------------- decoder J1939-71
# (signal, byte_awal(0-based), jumlah_byte, skala, offset, desimal)   nilai = raw*skala + offset
# Skala sebagai string Decimal -> hasil eksak, tanpa error float.
D = Decimal
J1939_SIGNALS = {
    61444: [("actual_torque_pct", 2, 1, D("1"), D("-125"), 0),            # EEC1  SPN 513
            ("engine_rpm", 3, 2, D("0.125"), D("0"), 3)],                 #       SPN 190
    61443: [("accel_pedal_pct", 1, 1, D("0.4"), D("0"), 1),               # EEC2  SPN 91
            ("engine_load_pct", 2, 1, D("1"), D("0"), 0)],                #       SPN 92
    65265: [("vehicle_speed_kmh", 1, 2, D("0.00390625"), D("0"), 3)],     # CCVS  SPN 84  (1/256 km/h)
    65262: [("coolant_temp_c", 0, 1, D("1"), D("-40"), 0),                # ET1   SPN 110
            ("fuel_temp_c", 1, 1, D("1"), D("-40"), 0),                   #       SPN 174
            ("oil_temp_c", 2, 2, D("0.03125"), D("-273"), 2)],            #       SPN 175
    65263: [("oil_pressure_kpa", 3, 1, D("4"), D("0"), 0)],               # EFL/P1 SPN 100
    65266: [("fuel_rate_lph", 0, 2, D("0.05"), D("0"), 2),                # LFE   SPN 183
            ("fuel_econ_inst_kmpl", 2, 2, D("0.001953125"), D("0"), 3)],  #       SPN 184 (1/512 km/L)
    65270: [("boost_pressure_kpa", 1, 1, D("2"), D("0"), 0),              # IC1   SPN 102
            ("intake_manifold_temp_c", 2, 1, D("1"), D("-40"), 0)],       #       SPN 105
    65271: [("battery_v", 4, 2, D("0.05"), D("0"), 2)],                   # VEP1  SPN 168
    65276: [("fuel_level_pct", 1, 1, D("0.4"), D("0"), 1)],               # DD    SPN 96
    65253: [("engine_hours", 0, 4, D("0.05"), D("0"), 2)],                # HOURS SPN 247
    65248: [("total_distance_km", 4, 4, D("0.125"), D("0"), 3)],          # VD    SPN 245
    61441: [("brake_pedal_pct", 1, 1, D("0.4"), D("0"), 1)],              # EBC1  SPN 521 (tambahan, verifikasi vs raw)
}
# raw >= nilai ini = "error / not available" menurut J1939-71 (0xFB.. s/d 0xFF..)
NA_MIN = {1: 0xFB, 2: 0xFB00, 4: 0xFB000000}


def parse_id(can_id: int):
    """29-bit J1939 ID -> (pgn, sa, prio). PDU1 (PF<240): PS = alamat tujuan, bukan bagian PGN."""
    prio = (can_id >> 26) & 0x07
    sa = can_id & 0xFF
    pf = (can_id >> 16) & 0xFF
    ps = (can_id >> 8) & 0xFF
    pgn = (((can_id >> 24) & 0x03) << 16) | (pf << 8)      # EDP + DP + PF
    if pf >= 240:
        pgn |= ps
    return pgn, sa, prio


def decode_frame(pgn: int, data: bytes):
    """-> list[(nama_sinyal, nilai_string)]. Nilai NA/error dilewati."""
    spec = J1939_SIGNALS.get(pgn)
    if not spec or len(data) < 8:
        return []
    out = []
    for name, start, size, scale, offset, dec in spec:
        raw = int.from_bytes(data[start:start + size], "little")
        if raw >= NA_MIN[size]:
            continue
        val = Decimal(raw) * scale + offset
        out.append((name, f"{val.quantize(Decimal(1).scaleb(-dec), rounding=ROUND_HALF_UP):f}"))
    return out


# ----------------------------------------------------------------------------- multi-paket (J1939-21 TP), DM1/DM2, ident
TP_CM, TP_DT = 60416, 60160                       # 0xEC00 (connection mgmt), 0xEB00 (data transfer); PS = alamat tujuan
PGN_DM1, PGN_DM2 = 65226, 65227                   # 0xFECA, 0xFECB
PGN_VI, PGN_CI, PGN_SOFT = 65260, 65259, 65242    # VIN 0xFEEC, Component ID 0xFEEB, Software ID 0xFEDA
MSG_PGNS = {PGN_DM1, PGN_DM2, PGN_VI, PGN_CI, PGN_SOFT}
EXTRA_PGNS = MSG_PGNS | {TP_CM, TP_DT}
LAMP_TXT = {0: "off", 1: "on", 2: "rsv", 3: "na"}


class MultiPacket:
    """Perakit TP.CM (BAM / RTS) + TP.DT secara PASIF (logger tidak pernah kirim CTS/ACK).
    Kunci sesi = (SA pengirim, DA tujuan). Retransmisi paket (setelah CTS ulang) menimpa paket lama."""
    TIMEOUT_US = 5_000_000      # jauh di atas timer J1939 (T1..T4 <= 1.25 s) supaya tidak salah buang

    def __init__(self):
        self.s = {}
        self.stats = collections.Counter()

    def reset(self):
        self.stats["timeout"] += len(self.s)
        self.s.clear()

    def feed(self, pgn, sa, can_id, data, t_us):
        """-> (pgn_pesan, sa, da, bytes) bila satu pesan multi-paket lengkap, selain itu None."""
        da = (can_id >> 8) & 0xFF
        if len(data) < 8:
            return None
        if pgn == TP_CM:
            ctl = data[0]
            if ctl in (16, 32):                                   # 16 = RTS, 32 = BAM
                key = (sa, da)
                if key in self.s:                                 # sesi lama belum selesai, diganti
                    self.stats["timeout"] += 1
                size = data[1] | (data[2] << 8)
                npk = data[3]
                mpgn = data[5] | (data[6] << 8) | (data[7] << 16)
                if not (9 <= size <= 1785) or npk != (size + 6) // 7:
                    self.s.pop(key, None)
                    self.stats["bad"] += 1
                    return None
                self.s[key] = {"pgn": mpgn, "size": size, "n": npk, "pk": {}, "t": t_us}
                self.stats["started"] += 1
            elif ctl == 255:                                      # abort (dari pengirim ATAU penerima)
                for key in ((sa, da), (da, sa)):
                    if self.s.pop(key, None) is not None:
                        self.stats["timeout"] += 1
            return None                                           # 17 = CTS, 19 = EndOfMsgACK: tak perlu
        # TP.DT
        key = (sa, da)
        st = self.s.get(key)
        if st is None:
            return None
        if t_us < st["t"] or t_us - st["t"] > self.TIMEOUT_US:
            del self.s[key]
            self.stats["timeout"] += 1
            return None
        seq = data[0]
        if not 1 <= seq <= st["n"]:
            return None
        st["pk"][seq] = bytes(data[1:8])
        st["t"] = t_us
        if len(st["pk"]) < st["n"]:
            return None
        buf = b"".join(st["pk"][i] for i in range(1, st["n"] + 1))[: st["size"]]
        del self.s[key]
        self.stats["completed"] += 1
        return st["pgn"], sa, da, buf


def decode_dm(data: bytes):
    """DM1/DM2 (J1939-73) -> (lamps, [(spn, fmi, oc, cm)]) atau None.
    Byte 0: bit7-6 MIL, bit5-4 Red Stop, bit3-2 Amber Warning, bit1-0 Protect (0=off 1=on 3=n/a); byte 1: flash.
    Tiap DTC 4 byte: SPN[7:0], SPN[15:8], SPN[18:16]<<5 | FMI(5 bit), CM<<7 | OC(7 bit).
    DTC 'tidak ada' (SPN=0,FMI=0,OC=0) dan padding 0xFF dilewati."""
    if len(data) < 2:
        return None
    b0 = data[0]
    lamps = {"mil": (b0 >> 6) & 3, "red": (b0 >> 4) & 3, "amber": (b0 >> 2) & 3, "protect": b0 & 3}
    dtcs = []
    for i in range(2, len(data) - 3, 4):
        b = data[i:i + 4]
        if b == b"\xff\xff\xff\xff":
            continue
        spn = b[0] | (b[1] << 8) | (((b[2] >> 5) & 7) << 16)
        fmi = b[2] & 0x1F
        oc = b[3] & 0x7F
        cm = b[3] >> 7
        if spn == 0 and fmi == 0 and oc == 0:
            continue
        dtcs.append((spn, fmi, oc, cm))
    return lamps, dtcs


def _ascii(b: bytes) -> str:
    return "".join(chr(x) if 32 <= x < 127 else "." for x in b)


def decode_ident(pgn: int, data: bytes):
    """-> (kind, value). Field ASCII dipisah '*'; sisa padding 0xFF dibuang."""
    if pgn == PGN_VI:
        return "VIN", _ascii(data.split(b"*")[0]).rstrip(".")
    if pgn == PGN_CI:                                   # make*model*serial*unit*
        parts = data.split(b"*")
        parts = parts[:-1] if len(parts) > 1 else parts
        return "COMPONENT_ID", "|".join(_ascii(p) for p in parts)
    if pgn == PGN_SOFT:                                 # byte0 = jumlah field, lalu field*field*...
        parts = data[1:].split(b"*")
        parts = parts[:-1] if len(parts) > 1 else parts
        return "SOFTWARE_ID", "|".join(_ascii(p) for p in parts)
    return "", ""


def dtc_rows(ctx, sa, msg, lamps, dtcs):
    """ctx = (pc_time, boot, seq, t_us). Satu baris per DTC; kalau kosong: satu baris dengan spn/fmi/oc/cm kosong."""
    la = [LAMP_TXT[lamps[k]] for k in ("mil", "red", "amber", "protect")]
    if not dtcs:
        return [[*ctx, sa, msg, *la, "", "", "", ""]]
    return [[*ctx, sa, msg, *la, spn, fmi, oc, cm] for spn, fmi, oc, cm in dtcs]


class J1939Extras:
    """Merakit multi-paket lalu men-decode DM1/DM2 dan ident. Callback dipanggil HANYA saat isi berubah."""

    def __init__(self, on_dtc, on_ident):
        self.tp = MultiPacket()
        self.on_dtc, self.on_ident = on_dtc, on_ident
        self.last = {}

    def reset(self):
        self.tp.reset()

    def feed(self, pgn, sa, can_id, data, t_us, ctx):
        if pgn == TP_CM or pgn == TP_DT:
            msg = self.tp.feed(pgn, sa, can_id, data, t_us)
            if msg is None:
                return
            pgn, sa, _da, data = msg
            if pgn not in MSG_PGNS:                 # PGN lain: sudah tersimpan di raw.csv, tak perlu decode
                return
        if pgn in (PGN_DM1, PGN_DM2):
            r = decode_dm(data)
            if r is None:
                return
            lamps, dtcs = r
            name = "DM1" if pgn == PGN_DM1 else "DM2"
            state = (tuple(lamps.values()), tuple(sorted(dtcs)))
            if self.last.get((sa, name)) != state:
                self.last[(sa, name)] = state
                self.on_dtc(ctx, sa, name, lamps, dtcs)
        elif pgn in MSG_PGNS:
            kind, value = decode_ident(pgn, data)
            if value and self.last.get((sa, kind)) != value:
                self.last[(sa, kind)] = value
                self.on_ident(ctx, sa, kind, value)


def crc8(data: bytes) -> int:
    """CRC-8 poly 0x07 init 0 -- identik dengan crc8() di firmware."""
    crc = 0
    for b in data:
        crc ^= b
        for _ in range(8):
            crc = ((crc << 1) ^ 0x07) & 0xFF if crc & 0x80 else (crc << 1) & 0xFF
    return crc


def term_cols() -> int:
    return max(40, shutil.get_terminal_size((120, 24)).columns - 1)


def who_holds(port):
    """Coba tampilkan proses yang memegang port (fuser/lsof)."""
    for cmd in (["fuser", "-v", port], ["lsof", port]):
        try:
            r = subprocess.run(cmd, capture_output=True, text=True, timeout=3)
            out = (r.stdout + r.stderr).strip()
            if out:
                return "\n".join("      " + ln for ln in out.splitlines())
        except (OSError, subprocess.SubprocessError):
            continue
    return ""


def port_hint(err, port):
    e = err.lower()
    if "busy" in e or "errno 16" in e:
        h = who_holds(port)
        return ("    -> Port SEDANG DIPAKAI proses lain. Tutup: Serial Monitor Arduino IDE, screen/minicom, logger lain.\n"
                "       ModemManager juga sering menyomot /dev/ttyACM*:  sudo systemctl stop ModemManager\n"
                + (f"       Pemegang port saat ini:\n{h}" if h else
                   f"       Cek pemegang:  sudo fuser -v {port}   atau   sudo lsof {port}"))
    if "permission" in e or "errno 13" in e:
        return "    -> Tidak ada izin: sudo usermod -aG dialout $USER  (lalu logout/login)"
    if "no such file" in e or "errno 2" in e:
        return ("    -> Port tidak ada. Jalankan: python3 log_to_csv.py --list   (adaptor USB-TTL = ttyUSB*, STM32 USB = ttyACM*)\n"
                "       Baru dicolok? cek: dmesg | tail")
    return ""


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
        self.w_dtc = opencsv("dtc.csv", DTC_HDR)
        self.w_id = opencsv("ident.csv", ID_HDR)
        self.w_st = opencsv("status.csv", ST_HDR)
        self.f_ev = opentxt("events.log")
        self.f_bad = opentxt("bad_lines.log")

    def raw(self, row):
        if self.enabled: self.w_raw.writerow(row)

    def dec(self, row):
        if self.enabled: self.w_dec.writerow(row)

    def dtc(self, row):
        if self.enabled: self.w_dtc.writerow(row)

    def ident(self, row):
        if self.enabled: self.w_id.writerow(row)

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
        self.rpm = {}                    # sa -> {n, t0, t1, min, max}  (hanya RPM VALID dari EEC1)
        self.eec1_sas = collections.Counter()   # sa -> jumlah frame EEC1 (termasuk yang nilainya NA)
        self.extras = J1939Extras(self.on_dtc, self.on_ident)
        self.extras_boot = 0
        self.bench_warned = False
        self.boot, self.expected = 0, None
        self.st, self.st_prev = None, None
        self.total_bytes, self.last_byte_ts = 0, None
        self.connected, self.t_connect, self.ever_connected = False, None, False
        self.first_pending = False      # baris pertama setelah (re)connect biasanya terpotong -> bukan error
        self.fw_msgs = []
        self.crumbs = []
        self.t0 = time.time()
        self.last_console = self.last_flush = self.last_sync = self.t0
        self.last_raw_cnt = 0
        self.last_idle_warn = self.last_garbage_warn = 0.0
        self.tty = sys.stdout.isatty()

    # ---- output helpers
    def say(self, msg):
        if self.tty:
            sys.stdout.write("\r" + " " * term_cols() + "\r")
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

    def in_grace(self, ts):
        return self.t_connect is not None and (ts - self.t_connect) < STARTUP_GRACE_S

    def reject(self, ts, kind, text):
        """Baris gagal validasi. Kalau ini baris pertama setelah connect (atau masih di jendela awal) -> potongan data lama, bukan korupsi."""
        if self.first_pending or self.in_grace(ts):
            self.first_pending = False
            self.c["partial_start"] += 1
            self.rec.bad(ts, "PARTIAL-START", text)
            return
        self.bad(ts, kind, text)

    # ---- callback dari J1939Extras (hanya saat isi berubah)
    def on_dtc(self, ctx, sa, msg, lamps, dtcs):
        for row in dtc_rows(ctx, sa, msg, lamps, dtcs):
            self.rec.dtc(row)
        self.c["dtc_changes"] += 1
        la = " ".join(f"{k}={LAMP_TXT[v]}" for k, v in lamps.items())
        txt = ", ".join(f"SPN {s}/FMI {f} (x{o})" for s, f, o, _ in dtcs) or "tidak ada DTC"
        self.event(time.time(), "DTC", f"{msg} SA={sa} [{la}] {txt}")

    def on_ident(self, ctx, sa, kind, value):
        self.rec.ident([*ctx, sa, kind, value])
        self.c["ident"] += 1
        self.event(time.time(), "ID", f"SA={sa} {kind}: {value}")

    # ---- item dari reader thread
    def on_item(self, item):
        kind, ts, payload = item
        if kind == "data":
            self.feed(ts, payload)
        elif kind == "connected":
            self.connected, self.t_connect, self.last_byte_ts, self.ever_connected = True, ts, ts, True
            self.first_pending = True
            self.event(ts, "INFO", f"serial terhubung: {payload}")
            self.say(f"[OK] serial terbuka: {payload}")
        elif kind == "disconnected":
            self.connected = False
            self.buf.clear()
            self.event(ts, "ERROR", f"serial putus: {payload} (auto-reconnect...)")
        elif kind == "waiting":
            self.say(f"[..] menunggu port: {payload}")
            h = port_hint(payload, self.meta.get("port", ""))
            if h:
                self.say(h)

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
        plain = text.lstrip("\ufffd\x00\x1b ")  # buang karakter sampah di awal (efek buka port)
        if plain.startswith("#"):
            self.first_pending = False
            msg = plain[1:].strip()
            self.c["plain"] += 1
            self.crumbs.append(msg)
            self.rec.event(ts, "FW-#", f"boot={self.boot} {msg}")
            self.say(f"[FW#] {msg}")
            return
        star = text.rfind("*")
        if star < 1 or len(text) - star - 1 != 2:
            return self.reject(ts, "FORMAT", text)
        payload, ck = text[:star], text[star + 1:]
        try:
            ok = int(ck, 16) == crc8(payload.encode("ascii", errors="replace"))
        except ValueError:
            ok = False
        if not ok:
            return self.reject(ts, "CRC", text)

        typ = payload[:1]
        if typ in "IWE":
            f = payload.split(",", 3)
            need = 4
        else:
            f = payload.split(",")
            need = {"R": 7, "S": 16}.get(typ, -1)
        if len(f) != need:
            return self.reject(ts, "FIELDS", text)
        try:
            seq, t_us = int(f[1]), int(f[2])
        except ValueError:
            return self.reject(ts, "FORMAT", text)

        frame = None
        if typ == "R":      # R,seq,t_us,ext,idhex8,dlc,datahex
            try:
                ext, idhex, dlc_s, data_hex = f[3], f[4], f[5], f[6]
                if ext not in ("0", "1") or len(idhex) != 8:
                    raise ValueError
                can_id, dlc = int(idhex, 16), int(dlc_s)
                if not 0 <= dlc <= 8 or len(data_hex) != 2 * dlc:
                    raise ValueError
                frame = (ext, idhex, can_id, dlc, data_hex, bytes.fromhex(data_hex))
            except ValueError:
                return self.reject(ts, "FIELDS", text)

        self.first_pending = False
        self.c["valid"] += 1
        self.check_seq(ts, typ, seq, f[3] if typ in "IWE" else "")
        p = iso(ts)

        if typ == "R":
            ext, idhex, can_id, dlc, data_hex, data = frame
            self.c["raw"] += 1
            if ext == "1":
                pgn, sa, prio = parse_id(can_id)
                self.pgns[(pgn, sa)] += 1
                if pgn == PGN_EEC1:
                    self.c["eec1_frames"] += 1
                    self.eec1_sas[sa] += 1
                for name, val in decode_frame(pgn, data):
                    self.c["dec"] += 1
                    self.rec.dec([p, self.boot, seq, t_us, sa, name, val])
                    self.latest[name] = val
                    if pgn == PGN_EEC1 and name == "engine_rpm":
                        v = float(val)
                        r = self.rpm.get(sa)
                        if r is None:
                            self.rpm[sa] = {"n": 1, "t0": t_us, "t1": t_us, "min": v, "max": v}
                        else:
                            r["n"] += 1
                            r["t1"] = t_us
                            r["min"] = min(r["min"], v)
                            r["max"] = max(r["max"], v)
                if pgn in EXTRA_PGNS:
                    if self.extras_boot != self.boot:       # MCU reboot -> t_us mulai dari nol, buang sesi TP lama
                        self.extras.reset()
                        self.extras_boot = self.boot
                    self.extras.feed(pgn, sa, can_id, data, t_us, (p, self.boot, seq, t_us))
            else:
                pgn = sa = prio = 0
            self.rec.raw([p, self.boot, seq, t_us, ext, idhex, pgn, sa, prio, dlc, data_hex])
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
            if diff and diff < 0x80000000 and self.in_grace(ts):
                self.c["stale_start"] += 1
                self.c["stale_lines"] += diff
                self.event(ts, "INFO", f"awal sesi: lompatan seq {self.expected}->{seq} ({diff} baris) = data lama/basi dari buffer "
                                       f"USB-serial / data yang lewat saat port tertutup, BUKAN kehilangan di dalam sesi", show=False)
            elif diff and diff < 0x80000000:
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
        if st["opmod"] == 0 and not self.bench_warned:
            self.bench_warned = True
            self.event(ts, "WARN", "MCU dalam mode NORMAL (BENCH): chip AKTIF di bus (memberi ACK). "
                                   "JANGAN di truk -> python3 log_to_csv.py <port> --bench 0")
        elif st["opmod"] != 0:
            self.bench_warned = False
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
                if self.c["plain"]:
                    last = self.crumbs[-1] if self.crumbs else "?"
                    self.say(STALL_HELP.format(last=last, hint=stage_hint(last)))
                else:
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
            if not self.connected:
                line = (f"{line[:10]} SERIAL TIDAK TERHUBUNG ke {self.meta.get('port', '?')} "
                        f"-- lihat pesan [..] di atas. Data TIDAK direkam.")
            if self.tty:
                w = term_cols()
                sys.stdout.write("\r" + line.ljust(w)[:w])
                sys.stdout.flush()
            elif int(now) % 5 == 0:
                print(line, flush=True)

    def write_meta(self, final=False):
        if not self.rec.enabled:
            return
        meta = dict(self.meta, host=socket.gethostname(), updated=iso(time.time()), final=final,
                    boots=self.boot, counters=dict(self.c), total_bytes=self.total_bytes, last_status=self.st,
                    tp=dict(self.extras.tp.stats),
                    rpm_sources={f"0x{sa:02X}": r for sa, r in self.rpm.items()})
        tmp = os.path.join(self.rec.folder, "session.json.tmp")
        with open(tmp, "w") as fh:
            json.dump(meta, fh, indent=2)
            fh.flush()
            os.fsync(fh.fileno())
        os.replace(tmp, os.path.join(self.rec.folder, "session.json"))

    # ---- ringkasan
    @staticmethod
    def rpm_hz(r):
        dt = (r["t1"] - r["t0"]) / 1e6
        return (r["n"] - 1) / dt if dt > 0 and r["n"] > 1 else 0.0

    def summary(self):
        c = self.c
        out = ["", "=" * 60, "RINGKASAN"]
        if not self.ever_connected and self.rec.enabled:
            out.append("  !! SERIAL TIDAK PERNAH TERHUBUNG -> tidak ada data yang terekam (folder sesi kosong).")
        out += [
               f"  byte diterima      : {self.total_bytes}",
               f"  baris valid        : {c['valid']}   (rusak/CRC: {c['bad']}, potongan awal: {c['partial_start']})",
               f"  frame CAN (raw)    : {c['raw']}",
               f"  sinyal decode      : {c['dec']}",
               f"  gap seq            : {c['gaps']} kejadian, {c['lost_lines']} baris hilang",
               f"  MCU reboot         : {max(self.boot - 1, 0)}"]
        if c["stale_start"] or c["partial_start"]:
            out.append(f"  awal sesi (diabaikan): {c['partial_start']} potongan baris, {c['stale_start']} lompatan seq "
                       f"({c['stale_lines']} baris) = data lama dari buffer USB-serial, bukan loss di dalam sesi")
        tp = self.extras.tp.stats
        out.append(f"  multi-paket (TP)   : {tp['completed']} pesan utuh dari {tp['started']} mulai, "
                   f"{tp['timeout'] + len(self.extras.tp.s)} tidak lengkap/menggantung, {tp['bad']} header invalid")
        out.append(f"  DTC / ident        : {c['dtc_changes']} perubahan DM1/DM2 (dtc.csv), "
                   f"{c['ident']} ident (ident.csv)")
        if self.st:
            s = self.st
            out.append(f"  status MCU terakhir: rx_total={s['rx_total']} ring_drop={s['ring_drop']} hw_ovr={s['hw_ovr']} "
                       f"eflg=0x{s['eflg']:02X} rec={s['rec']} tec={s['tec']} loop_max={s['loop_max_us']}us opmod={s['opmod']}")
        if self.rpm:
            out.append("  RPM valid dari EEC1 (SPN 190):")
            for sa, r in sorted(self.rpm.items()):
                out.append(f"    SA={sa} (0x{sa:02X})  {r['n']} frame  ~{self.rpm_hz(r):.1f} Hz  "
                           f"min={r['min']:.0f} max={r['max']:.0f} rpm")
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
            if c["plain"]:
                last = self.crumbs[-1] if self.crumbs else "?"
                return False, STALL_HELP.format(last=last, hint=stage_hint(last))
            return False, "Ada byte tapi tidak ada baris valid.\n" + GARBAGE_HELP.format(s=bytes(self.sample[:60]))
        errs = [m for t, m in self.fw_msgs if t == "E"]
        if errs:
            return False, "MCU komunikasi OK tapi init CAN GAGAL:\n  - " + "\n  - ".join(dict.fromkeys(errs))
        if not c["raw"]:
            return False, ("Komunikasi MCU OK, chip MCP2515 OK, tapi TIDAK ADA FRAME CAN.\n"
                           "  Firmware sudah mencoba scan 250/500k x xtal 8/16MHz (lihat baris 'scan cfg=' di atas).\n"
                           "  Cek: kontak mobil ON | CAN-H/CAN-L tidak tertukar & memang bus J1939 | GND bersama\n"
                           "  | jumper terminasi 120 ohm HW-184 DILEPAS di truk | konektor/pin diagnostik yang benar.")
        if c["bad"] or c["gaps"]:
            return False, f"Frame masuk tapi ada baris rusak ({c['bad']}) / gap ({c['gaps']}): cek kabel USB-TTL / baud."
        if self.st and (self.st["ring_drop"] or self.st["hw_ovr"]):
            return False, "Frame masuk tapi MCU melaporkan DATA LOSS (ring_drop/hw_ovr > 0)."
        if not self.rpm:
            if c["eec1_frames"]:
                sas = ", ".join(f"0x{sa:02X}" for sa in sorted(self.eec1_sas))
                return False, (f"EEC1 (PGN 61444) terlihat dari SA {sas} ({c['eec1_frames']} frame), tapi nilai RPM-nya "
                               "SELALU 'error / not available' (raw >= 0xFB00).\n"
                               "  Mesin mati / ECU belum siap? Ulangi saat mesin hidup. Kalau tetap begitu, "
                               "RPM di bus ini bukan dari EEC1 -> cek titik colok.")
            return False, (f"Frame CAN masuk ({c['raw']}, {c['dec']} sinyal ter-decode) tapi TIDAK ADA EEC1 "
                           "(PGN 61444, ID 29-bit 0x0CF004xx) -> RPM TIDAK tersedia di bus ini.\n"
                           "  Lihat daftar PGN di atas. Kemungkinan salah titik colok / jaringan CAN lain (ECB/VDB/dll) "
                           "-> coba titik lain sebelum menyalahkan kode.")
        parts = []
        for sa, r in sorted(self.rpm.items()):
            parts.append(f"SA=0x{sa:02X} ({r['n']} frame, ~{self.rpm_hz(r):.0f} Hz, {r['min']:.0f}-{r['max']:.0f} rpm)")
        msg = ("SEMUA OK: komunikasi, self-test, frame CAN, EEC1/RPM ter-decode, tanpa loss/gap.\n"
               "  RPM dari " + "; ".join(parts) + "\n"
               "  -> isi RPM_SA_FILTER di firmware buzzer dengan SA ini.")
        if all(r["max"] == 0 for r in self.rpm.values()):
            msg += "\n  CATATAN: RPM selalu 0 (mesin mati?). Skala RPM baru tervalidasi kalau dibandingkan dgn takometer saat mesin hidup."
        if len(self.rpm) > 1:
            msg += "\n  CATATAN: >1 SA mengirim RPM valid -> pilih satu (biasanya ECU mesin), jangan biarkan filter 'ANY'."
        if c["stale_start"] or c["partial_start"]:
            msg += "\n  CATATAN: di awal sesi ada data lama/potongan dari buffer USB-serial (diabaikan; bukan loss di dalam sesi)."
        if self.st and self.st["opmod"] == 0:
            msg += ("\n  !! PERINGATAN: MCU dalam mode BENCH (NORMAL-ACK, aktif di bus). Hanya untuk meja test; "
                    "sebelum ke truk: --bench 0 (atau reset board).")
        if self.st and self.st["line_drop"]:
            msg += f"\n  CATATAN: line_drop={self.st['line_drop']} (baris info/status MCU terbuang; frame R tidak terpengaruh)."
        return True, msg


NO_BYTES_HELP = """[!] Tidak ada byte dari MCU selama {n:.0f}s. Kemungkinan:
    1. Port salah        -> python3 log_to_csv.py --list   (firmware v3.0 keluar di Serial2/USART2 lewat adaptor USB-TTL
                            = biasanya /dev/ttyUSB0, BUKAN /dev/ttyACM0)
    2. Arduino IDE       -> Tools > USB support = "CDC (generic 'Serial' supersede U(S)ART)"; kalau tidak,
                            Serial = USART1 (PA9/PA10), bukan USB. Re-upload setelah ganti.
    3. Adaptor USB-TTL   -> PA2 (TX2) -> RX adaptor, GND bersama; baud firmware (LOG_BAUD) harus sama dg -b (default 921600).
                            Teks kotak-kotak = baud beda. Adaptor tidak kuat 921600? pakai 460800 di firmware DAN Python
                            (cukup untuk bus <= ~700 frame/s).
    4. Kabel USB charge-only / board belum ter-flash -> ganti kabel, upload ulang
    5. LED PC13          -> mati terus = firmware tidak jalan; kedip 5Hz = init/scan bitrate; blip 1/detik = hidup tapi bus sepi
    6. Permission        -> sudo usermod -aG dialout $USER  (lalu logout/login)
    7. Firmware macet sebelum sempat nge-print -> tekan RESET di board saat logger jalan (breadcrumb
                            "# stage ..." muncul tiap tahap)"""
STALL_HELP = """[!] MCU HIDUP (mengirim breadcrumb) tapi protokol logger belum jalan.
    Breadcrumb terakhir : {last}
    Artinya             : {hint}"""
GARBAGE_HELP = """[!] Ada byte masuk tapi bukan protokol logger (contoh: {s!r}).
    Kemungkinan baud salah (harus sama dengan LOG_BAUD, default 921600), firmware lama (v2.x format beda), atau port dipakai program lain."""


def stage_hint(last: str) -> str:
    l = last.lower()
    if "stage 1/5" in l or "stage 1 gagal" in l:
        return "berhenti/gagal di CAN0.begin(): SPI atau modul MCP2515 (cek CS=PA4 SCK=PA5 MISO=PA6 MOSI=PA7, VCC=5V, GND)."
    if "stage 2" in l:
        return "berhenti saat baca register langsung (raw SPI). Library OK tapi jalur mentah bermasalah."
    if "stage 3" in l:
        return "berhenti di self-test loopback internal: chip/SPI tidak stabil."
    if "stage 4" in l:
        return "berhenti saat scan bitrate / set mode CAN."
    if "stage 5" in l or "ready" in l:
        return "init selesai; harusnya baris protokol sudah jalan -> cek LOG_BAUD/serial."
    if "boot" in l:
        return "firmware baru boot lalu berhenti sebelum stage 1: curiga macet di inisialisasi awal."
    return "tahap tidak dikenal."


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
                    kw = {"exclusive": True} if os.name == "posix" else {}  # cegah dua proses rebutan port
                    ser = serial.Serial(self.port, self.baud, timeout=0.1, **kw)
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
        hint = {"0483:5740": "  <-- STM32 USB CDC", "1A86:7523": "  <-- adaptor CH340 (USB-TTL)",
                "10C4:EA60": "  <-- adaptor CP210x (USB-TTL)", "0403:6001": "  <-- adaptor FTDI (USB-TTL)"}.get(vp, "")
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


def send_bench(port, baud, value, timeout=6.0, retry=2.0):
    """Kirim 'bench 0|1' ke firmware logger lewat serial. TIDAK merekam. -> exit code.
    Sukses kalau (a) firmware membalas 'BENCH=<value> ...' ATAU (b) baris status S dari MCU menunjukkan opmod yang diminta
    (1 -> opmod 0 NORMAL, 0 -> opmod 3 LISTEN-ONLY). Jalur (b) perlu karena baris info bisa terbuang saat serial penuh.
    Perintah dikirim ulang tiap `retry` detik (aman: idempotent) sampai `timeout`."""
    try:
        kw = {"exclusive": True} if os.name == "posix" else {}
        ser = serial.Serial(port, baud, timeout=0.2, **kw)
    except (serial.SerialException, OSError, ValueError) as e:
        print(f"[ERR] tidak bisa membuka {port}: {e}")
        h = port_hint(str(e), port)
        if h:
            print(h)
        return 1
    want, want_opmod = f"BENCH={value}", (0 if value == 1 else 3)
    buf, replies = bytearray(), []
    n_bytes = n_valid = 0
    last_opmod = None
    sent_at, sends, ok_via = 0.0, 0, None
    t_end = time.time() + timeout
    with ser:
        ser.reset_input_buffer()
        while time.time() < t_end and ok_via is None:
            now = time.time()
            if sends == 0 or now - sent_at >= retry:
                ser.write(f"bench {value}\n".encode("ascii"))
                ser.flush()
                sent_at, sends = now, sends + 1
            data = ser.read(max(1, ser.in_waiting))
            n_bytes += len(data)
            buf += data
            while True:
                i = buf.find(b"\n")
                if i < 0:
                    break
                text = bytes(buf[:i]).decode("ascii", errors="replace").rstrip("\r")
                del buf[: i + 1]
                star = text.rfind("*")
                if star < 1 or len(text) - star - 1 != 2 or text[:1] not in "IWERS":
                    continue
                payload = text[:star]
                try:
                    if int(text[star + 1:], 16) != crc8(payload.encode("ascii", errors="replace")):
                        continue
                except ValueError:
                    continue
                n_valid += 1
                typ = payload[:1]
                if typ in "IWE":
                    parts = payload.split(",", 3)
                    if len(parts) == 4 and "BENCH=" in parts[3]:
                        replies.append(parts[3])
                        print(f"[FW-{typ}] {parts[3]}")
                        if want in parts[3] and "GAGAL" not in parts[3]:
                            ok_via = "balasan firmware"
                elif typ == "S":
                    f = payload.split(",")
                    if len(f) == 16 and f[15].isdigit() and int(f[15]) != 255:
                        last_opmod = int(f[15])
                        if sends and last_opmod == want_opmod:
                            ok_via = f"status MCU (opmod={last_opmod})"
    if ok_via:
        print(f"[OK] bench {value} berhasil, terkonfirmasi lewat {ok_via}.")
        return 0
    if replies:
        print(f"[FAIL] bench {value} TIDAK berhasil (lihat balasan firmware di atas).")
    elif n_bytes == 0:
        print(f"[FAIL] tidak ada byte sama sekali dari MCU (perintah dikirim {sends}x). Board mati/ter-reset/belum siap, "
              "port atau baud salah, atau ST-Link VCP belum terhubung. Coba: --check, cek LED, cabut-colok USB.")
    elif n_valid == 0:
        print(f"[FAIL] ada {n_bytes} byte tapi bukan protokol logger -> baud salah (harus = LOG_BAUD, 921600) atau firmware lama.")
    else:
        print(f"[FAIL] MCU hidup dan mengirim data ({n_valid} baris valid, opmod terakhir={last_opmod}, "
              f"yang diminta={want_opmod}) tapi TIDAK membalas bench (perintah dikirim {sends}x).")
        print("       Kemungkinan: firmware belum v3.1 | jalur RX (PA3 <- TX adaptor/ST-Link) tidak sampai | "
              "baris balasan terbuang karena serial penuh (lihat kolom line_drop di status.csv).")
    return 1


def redecode(path):
    """Decode ulang raw.csv memakai tabel sinyal yang sekarang + rakit ulang multi-paket (DM1/DM2/ident).
    Tidak menyentuh raw.csv / decoded.csv / dtc.csv / ident.csv asli; file *_redecode.csv ditimpa tiap kali dijalankan."""
    raw_path = os.path.join(path, "raw.csv") if os.path.isdir(path) else path
    base = os.path.dirname(raw_path) or "."
    out_path = os.path.join(base, "decoded_redecode.csv")
    dtc_path = os.path.join(base, "dtc_redecode.csv")
    id_path = os.path.join(base, "ident_redecode.csv")
    if not os.path.isfile(raw_path):
        sys.exit(f"tidak ada file: {raw_path}")
    n_in = n_ext = n_out = n_dtc = n_id = 0
    with open(raw_path, newline="") as fi, open(out_path, "w", newline="") as fo, \
            open(dtc_path, "w", newline="") as fd, open(id_path, "w", newline="") as fid:
        w = csv.writer(fo, lineterminator="\n")
        wd = csv.writer(fd, lineterminator="\n")
        wi = csv.writer(fid, lineterminator="\n")
        w.writerow(DEC_HDR)
        wd.writerow(DTC_HDR)
        wi.writerow(ID_HDR)

        def on_dtc(ctx, sa, msg, lamps, dtcs):
            nonlocal n_dtc
            wd.writerows(dtc_rows(ctx, sa, msg, lamps, dtcs))
            n_dtc += 1

        def on_ident(ctx, sa, kind, value):
            nonlocal n_id
            wi.writerow([*ctx, sa, kind, value])
            n_id += 1

        ex = J1939Extras(on_dtc, on_ident)
        boot_seen = None
        for row in csv.DictReader(fi):
            n_in += 1
            if row["ext"] != "1":
                continue
            n_ext += 1
            can_id = int(row["can_id_hex"], 16)
            data = bytes.fromhex(row["data_hex"])
            pgn, sa, _ = parse_id(can_id)
            for name, val in decode_frame(pgn, data):
                w.writerow([row["pc_time"], row["boot"], row["seq"], row["t_us"], sa, name, val])
                n_out += 1
            if pgn in EXTRA_PGNS:
                if row["boot"] != boot_seen:
                    ex.reset()
                    boot_seen = row["boot"]
                ex.feed(pgn, sa, can_id, data, int(row["t_us"]),
                        (row["pc_time"], row["boot"], row["seq"], row["t_us"]))
    st = ex.tp.stats
    print(f"[OK] {n_in} frame raw ({n_ext} extended) -> {n_out} sinyal di {out_path}")
    print(f"[OK] multi-paket: {st['completed']} utuh / {st['started']} mulai ({st['timeout'] + len(ex.tp.s)} tidak lengkap/menggantung) | "
          f"DM1/DM2 berubah: {n_dtc} -> {dtc_path} | ident: {n_id} -> {id_path}")


def main():
    ap = argparse.ArgumentParser(description="J1939 logger host", formatter_class=argparse.RawDescriptionHelpFormatter,
                                 epilog=__doc__)
    ap.add_argument("port", nargs="?")
    ap.add_argument("-b", "--baud", type=int, default=921600, help="harus sama dengan LOG_BAUD firmware (default 921600)")
    ap.add_argument("-o", "--outdir", default="logs")
    ap.add_argument("-n", "--name")
    ap.add_argument("--overwrite", action="store_true")
    ap.add_argument("--check", type=int, nargs="?", const=10, metavar="DETIK")
    ap.add_argument("--monitor", action="store_true")
    ap.add_argument("--list", action="store_true")
    ap.add_argument("--redecode", metavar="FOLDER_ATAU_RAW_CSV")
    ap.add_argument("--bench", type=int, choices=(0, 1), help="kirim 'bench 0|1' ke firmware (1 = HANYA meja test), lalu keluar")
    args = ap.parse_args()

    if args.list:
        return list_all_ports()
    if args.redecode:
        return redecode(args.redecode)
    if not args.port:
        ap.error("port wajib (lihat: --list)")
    if args.bench is not None:
        sys.exit(send_bench(args.port, args.baud, args.bench))

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
