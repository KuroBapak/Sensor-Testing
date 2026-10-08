/*
 * J1939 PASSIVE LOGGER v3.1 -- STM32 (STM32duino) + HW-184 (MCP2515)
 * Target : Iveco Astra HD9, J1939 (250 kbps, bisa auto-scan 250/500k), mode LISTEN-ONLY (TIDAK PERNAH TX ke bus)
 *
 * BENCH MODE (meja test dengan node TX simulasi) = RUNTIME lewat serial, BUKAN compile-time:
 *   - DEFAULT 0 SETIAP BOOT/RESET (LISTEN-ONLY, aman untuk truk). TIDAK disimpan ke flash.
 *   - Perintah dikirim ke LOG_SERIAL (Serial2 = USART2, RX = PA3 <- TX adaptor USB-TTL), diakhiri newline:
 *       bench 1   -> mode NORMAL (memberi ACK, AKTIF di bus). HANYA meja test. JANGAN di truk.
 *       bench 0   -> kembali LISTEN-ONLY (pasif)
 *       bench     -> tampilkan status bench
 *   - Dari PC (tanpa terminal):  python3 log_to_csv.py <port> --bench 1   /   --bench 0
 *   - Reset / cabut daya -> otomatis kembali ke bench 0.
 *
 * Library : "MCP_CAN_lib" by coryjfowler
 * Wiring  : HW-184 VCC=5V, GND, CS=PA4, SCK=PA5, MISO=PA6, MOSI=PA7, INT=PB0 (opsional)
 * Serial  : data keluar lewat Serial2 = USART2 (PA2=TX, PA3=RX) -> adaptor USB-TTL / ST-Link VCP.
 *
 * WAJIB: file build_opt.h (satu folder dengan .ino ini) berisi  -DSERIAL_TX_BUFFER_SIZE=1024
 *        (default STM32duino cuma 64 byte -> throughput serial 921600 tidak tercapai).
 *        (Opsi compile-time -DBENCH_ACK_MODE sudah DIHAPUS; kalau masih ada di build_opt.h, diabaikan.)
 *
 * Perubahan v3.1 dibanding v3.0:
 *  - BENCH_ACK_MODE compile-time diganti perintah serial "bench 0|1" (default 0 tiap boot).
 *
 * Perubahan v3.0 dibanding v2.4:
 *  - LOG_BAUD 921600 (cocok dg default log_to_csv.py). 115200 cuma muat ~175 frame/s -> data hilang.
 *  - Decode J1939 DIPINDAH ke sisi host (log_to_csv.py). Firmware hanya kirim frame mentah:
 *    bandwidth jauh lebih hemat, tidak ada risiko overflow karena format teks, PGN baru tanpa re-flash.
 *  - Jalur data: serial dipompa & CAN dibaca bergantian per frame (jeda antar-baca MCP2515 < 1 ms).
 *  - loop_max_us kini = jeda terpanjang antar-servis MCP2515 (itu yang menentukan overflow).
 *  - AUTO_BITRATE: scan 250/500k x xtal 8/16MHz dalam mode LISTEN-ONLY (aman, tidak mengirim apa pun),
 *    kunci cfg yang bus-nya hidup. Scan ulang kalau bus sepi > RESCAN_SILENT_MS.
 *  - Pengaman: setelah tiap begin() mode chip dibaca; kalau library meninggalkan chip di NORMAL
 *    (aktif di bus) dan bench=0 -> dipaksa LISTEN-ONLY dan scan dimatikan.
 *  - crumb() tidak pernah menyelip di tengah baris.
 */

#include <SPI.h>
#include <mcp_can.h>

#define FW_VERSION         "3.1"

// ===================== KONFIGURASI =====================
#define CAN_CS_PIN         PA4
#define CAN_CS_TXT         "PA4"
#define CAN_INT_PIN        PB0
#define USE_INT_PIN        0            // 0 = polling via SPI (paling aman, INT tidak perlu dikabel)
#define AUTO_BITRATE       1            // 1 = scan 250/500k x 8/16MHz (LISTEN-ONLY) lalu kunci yang hidup
#define CAN_SPEED          CAN_250KBPS  // cfg pilihan pertama (dicoba dulu)
#define CAN_XTAL           MCP_8MHZ     // lihat tulisan di kristal modul: 8.000 atau 16.000
#define CAN_CFG_TXT        "250kbps/8MHz"
#define LOG_SERIAL         Serial2      // USART2 (PA2/PA3)
#define LOG_BAUD           921600       // HARUS sama dengan -b di log_to_csv.py
#define LED_PIN            PC13
#define LED_ACTIVE_LOW     1
#define SELFTEST_LOOPBACK  1            // tes chip MCP2515 secara internal (tidak menyentuh bus)
#define FRAME_RING_SIZE    256          // frame CAN yang ditahan di RAM
#define TX_RING_SIZE       4096         // byte serial yang ditahan di RAM
#define STATUS_PERIOD_MS   1000UL
#define SILENT_WARN_MS     3000UL
#define SCAN_WINDOW_MS     500UL        // lama mendengarkan per kandidat cfg
#define SCAN_MIN_FRAMES    15           // minimal frame extended dalam satu jendela
#define RESCAN_SILENT_MS   20000UL      // bus sepi selama ini -> scan ulang cfg
// =======================================================

#ifndef SERIAL_TX_BUFFER_SIZE
#define SERIAL_TX_BUFFER_SIZE 64
#endif
#if SERIAL_TX_BUFFER_SIZE < 256
#warning "SERIAL_TX_BUFFER_SIZE < 256: buat build_opt.h berisi -DSERIAL_TX_BUFFER_SIZE=1024 (lihat header)"
#endif

#define MODE_TXT_LISTEN  "LISTEN-ONLY"
#define MODE_TXT_BENCH   "NORMAL-ACK(BENCH)"

MCP_CAN CAN0(CAN_CS_PIN);

// MCP2515 register
#define REG_TEC     0x1C
#define REG_REC     0x1D
#define REG_CNF3    0x28
#define REG_CNF2    0x29
#define REG_CNF1    0x2A
#define REG_EFLG    0x2D
#define REG_CANSTAT 0x0E

static const char HEXCH[] = "0123456789ABCDEF";

// ---------- kandidat konfigurasi bitrate ----------
struct BitCfg { uint8_t speed; uint8_t xtal; const char *txt; };
static const BitCfg CFGS[] = {
  { CAN_SPEED,    CAN_XTAL,  CAN_CFG_TXT      },   // [0] pilihan user
  { CAN_250KBPS,  MCP_8MHZ,  "250kbps/8MHz"   },
  { CAN_250KBPS,  MCP_16MHZ, "250kbps/16MHz"  },
  { CAN_500KBPS,  MCP_8MHZ,  "500kbps/8MHz"   },
  { CAN_500KBPS,  MCP_16MHZ, "500kbps/16MHz"  },
};
#define NCFG ((uint8_t)(sizeof(CFGS) / sizeof(CFGS[0])))

// ---------- state ----------
struct Frame { uint64_t t_us; uint32_t id; uint8_t ext; uint8_t len; uint8_t data[8]; };
static Frame    fring[FRAME_RING_SIZE];
static uint16_t fHead = 0, fTail = 0, fCount = 0;

static uint8_t  txbuf[TX_RING_SIZE];
static uint16_t txHead = 0, txTail = 0, txCount = 0, txHwm = 0;
static bool     txUseAvail = true, txFallbackFlag = false;
static uint32_t txLastProgressMs = 0;

static uint32_t seqNo = 0;

struct Stats {
  uint32_t rxTotal, framesSec, fps, ringDrop, ringHwm, hwOvr, loopMaxUs, lineDrop, lastLossMs;
  bool lossEver;
};
static Stats    st;
static bool     canReady = false, everRx = false;
static uint32_t lastRxMs = 0, lastStatusMs = 0, lastInitTryMs = 0, lastSilentWarnMs = 0;
static uint32_t prevRingDrop = 0, prevHwOvr = 0, prevLineDrop = 0;
static uint32_t lastSvcUs = 0;
static uint8_t  lastEflg = 0;
static bool     rawOk = true;       // false = baca register langsung tidak cocok dg library -> mode degraded
static bool     autoOk = false;     // true = scan bitrate diizinkan (aman & terverifikasi)
static int8_t   lastGood = -1;      // indeks CFGS yang terakhir terbukti hidup
static uint8_t  curCfg = 0;         // indeks CFGS yang sedang terpasang di chip

// BENCH: HANYA di RAM. Nilai awal false (= LISTEN-ONLY, aman untuk truk) pada setiap boot/reset.
static bool     benchMode = false;
static inline uint8_t     runMode()    { return benchMode ? MCP_NORMAL : MCP_LISTENONLY; }
static inline uint8_t     runOpmod()   { return benchMode ? 0 : 3; }
static inline const char *runModeTxt() { return benchMode ? MODE_TXT_BENCH : MODE_TXT_LISTEN; }

// perintah serial (bench 0|1)
static char     cmdBuf[32];
static uint8_t  cmdLen = 0;

// ---------- util ----------
static uint64_t now64us() {
  static uint32_t last = 0, hi = 0;
  uint32_t m = micros();
  if (m < last) hi++;
  last = m;
  return ((uint64_t)hi << 32) | m;
}

static uint8_t crc8(const char *s, uint16_t n) {
  uint8_t c = 0;
  while (n--) {
    c ^= (uint8_t)*s++;
    for (uint8_t i = 0; i < 8; i++) c = (c & 0x80) ? (uint8_t)((c << 1) ^ 0x07) : (uint8_t)(c << 1);
  }
  return c;
}

static inline uint16_t txFree() { return TX_RING_SIZE - txCount; }

static void txPush(const char *b, uint16_t n) {
  for (uint16_t i = 0; i < n; i++) {
    txbuf[txHead] = (uint8_t)b[i];
    txHead = (txHead + 1) % TX_RING_SIZE;
  }
  txCount += n;
  if (txCount > txHwm) txHwm = txCount;
}

// ---------- pembentuk baris ----------
struct Line {
  char b[200];
  uint16_t n;
  Line &c(char ch) { if (n < 184) b[n++] = ch; return *this; }
  Line &s(const char *p) { while (*p && n < 184) b[n++] = *p++; return *this; }
  Line &u(uint64_t v) {
    char t[21]; uint8_t k = 0;
    do { t[k++] = '0' + (uint8_t)(v % 10); v /= 10; } while (v);
    while (k && n < 184) b[n++] = t[--k];
    return *this;
  }
  Line &hx(uint32_t v, uint8_t digits) {
    for (int8_t i = (digits - 1) * 4; i >= 0; i -= 4) c(HEXCH[(v >> i) & 0xF]);
    return *this;
  }
  void start(char type, uint64_t t_us) { n = 0; c(type).c(',').u(seqNo).c(',').u(t_us).c(','); }
  bool send() {
    uint8_t ck = crc8(b, n);
    b[n++] = '*'; b[n++] = HEXCH[ck >> 4]; b[n++] = HEXCH[ck & 15]; b[n++] = '\n';
    if (txFree() < n) { st.lineDrop++; return false; }
    txPush(b, n);
    seqNo++;
    return true;
  }
};

static void logMsg(char type, const char *msg) {
  Line l; l.start(type, now64us()); l.s(msg); l.send();
}

// ---------- LED & serial pump ----------
static void ledUpdate(uint32_t now) {
  bool on;
  if (!canReady)                                       on = ((now / 100) & 1);      // 5 Hz = init / scan bitrate
  else if (st.lossEver && (now - st.lastLossMs) < 5000) on = true;                   // nyala terus = baru ada data loss
  else if (!everRx || (now - lastRxMs) > 2000)          on = ((now % 1000) < 60);    // blip 1/detik = hidup, bus sepi
  else                                                  on = ((now / 250) & 1);      // 2 Hz = frame mengalir
  digitalWrite(LED_PIN, LED_ACTIVE_LOW ? !on : on);
}

static void pumpTx() {
  uint32_t nowMs = millis();
  if (!txCount) { txLastProgressMs = nowMs; return; }
  int room = txUseAvail ? LOG_SERIAL.availableForWrite() : 64;
  if (room <= 0) {
    if (txUseAvail && (nowMs - txLastProgressMs) > 500) { txUseAvail = false; txFallbackFlag = true; }
    return;
  }
  uint16_t n = ((uint16_t)room > txCount) ? txCount : (uint16_t)room;
  uint16_t contiguous = TX_RING_SIZE - txTail;
  if (n > contiguous) n = contiguous;
  size_t w = LOG_SERIAL.write(&txbuf[txTail], n);
  if (w) {
    txTail = (txTail + w) % TX_RING_SIZE;
    txCount -= w;
    txLastProgressMs = nowMs;
  }
}

static void waitMs(uint32_t ms) {
  uint32_t t0 = millis();
  while ((millis() - t0) < ms) { pumpTx(); ledUpdate(millis()); }
}

static void drainTx(uint32_t maxMs) {
  uint32_t t0 = millis();
  while (txCount && (millis() - t0) < maxMs) pumpTx();
}

// Teks bebas "# ..." hanya ditulis kalau ring sudah kosong -> tidak pernah menyelip di tengah baris protokol.
static void crumb(const char *msg) {
  drainTx(200);
  if (txCount) return;
  LOG_SERIAL.print("# ");
  LOG_SERIAL.println(msg);
}

// ---------- akses register MCP2515 langsung ----------
static uint8_t mcpRead(uint8_t reg) {
  if (!rawOk) return 0;
  digitalWrite(CAN_CS_PIN, LOW);
  SPI.transfer(0x03); SPI.transfer(reg);
  uint8_t v = SPI.transfer(0x00);
  digitalWrite(CAN_CS_PIN, HIGH);
  return v;
}
static void mcpModify(uint8_t reg, uint8_t mask, uint8_t val) {
  if (!rawOk) return;
  digitalWrite(CAN_CS_PIN, LOW);
  SPI.transfer(0x05); SPI.transfer(reg); SPI.transfer(mask); SPI.transfer(val);
  digitalWrite(CAN_CS_PIN, HIGH);
}

// ---------- frame mentah -> satu baris protokol ----------
// R,seq,t_us,ext,idhex8,dlc,datahex*CK   (PGN/SA/prio & decode sinyal dihitung di host)
static void emitFrame(const Frame &f) {
  Line l; l.start('R', f.t_us);
  l.u(f.ext).c(',').hx(f.id, 8).c(',').u(f.len).c(',');
  for (uint8_t i = 0; i < f.len; i++) l.hx(f.data[i], 2);
  l.send();
}

// ---------- jalur data ----------
static inline bool msgPending() {
#if USE_INT_PIN
  return digitalRead(CAN_INT_PIN) == LOW;
#else
  return CAN0.checkReceive() == CAN_MSGAVAIL;
#endif
}

static void drainCan(uint8_t maxFrames) {
  uint32_t us = micros();
  if (lastSvcUs) { uint32_t g = us - lastSvcUs; if (g > st.loopMaxUs) st.loopMaxUs = g; }
  lastSvcUs = us;
  for (uint8_t i = 0; i < maxFrames; i++) {
    if (!msgPending()) break;
    unsigned long id = 0; byte ext = 0, len = 0, buf[8];
    uint64_t t = now64us();
    if (CAN0.readMsgBuf(&id, &ext, &len, buf) != CAN_OK) break;
    if (len > 8) len = 8;
    st.rxTotal++; st.framesSec++;
    lastRxMs = millis(); everRx = true;
    if (fCount >= FRAME_RING_SIZE) {      // ring penuh: frame terbuang (dihitung, dilaporkan di baris S)
      st.ringDrop++; st.lossEver = true; st.lastLossMs = lastRxMs;
      continue;
    }
    Frame &f = fring[fHead];
    f.t_us = t; f.id = id & 0x1FFFFFFFUL; f.ext = ext ? 1 : 0; f.len = len;
    memcpy(f.data, buf, len);
    fHead = (fHead + 1) % FRAME_RING_SIZE;
    fCount++;
    if (fCount > st.ringHwm) st.ringHwm = fCount;
  }
}

// Satu frame diformat -> langsung dipompa ke serial -> MCP2515 dibaca lagi. Jeda antar-servis chip tetap pendek.
static void formatFrames(uint8_t maxN) {
  while (maxN-- && fCount && txFree() >= 256) {
    emitFrame(fring[fTail]);
    fTail = (fTail + 1) % FRAME_RING_SIZE;
    fCount--;
    pumpTx();
    drainCan(4);
  }
}

static void pollHw(uint32_t nowMs) {
  if (!rawOk) return;
  static uint32_t last = 0;
  if ((nowMs - last) < 20) return;
  last = nowMs;
  uint8_t e = mcpRead(REG_EFLG);
  lastEflg = e;
  if (e & 0xC0) {                          // RX0OVR / RX1OVR: buffer MCP2515 meluap (frame hilang di chip)
    st.hwOvr++; st.lossEver = true; st.lastLossMs = nowMs;
    mcpModify(REG_EFLG, 0xC0, 0x00);
  }
}

// Tunggu sambil TETAP membaca MCP2515 (transisi mode jangan jadi celah overflow yang tak terlapor).
static void settleMs(uint32_t ms) {
  uint32_t t0 = millis();
  while ((millis() - t0) < ms) { drainCan(8); pumpTx(); ledUpdate(millis()); }
}

// ---------- BENCH: perintah serial "bench 0|1" ----------
static void reportBench() {
  if (benchMode) logMsg('I', "BENCH=1 AKTIF: chip NORMAL (memberi ACK, aktif di bus). Hanya meja test, JANGAN di truk. Kirim 'bench 0' untuk kembali");
  else           logMsg('I', "BENCH=0: LISTEN-ONLY (pasif, aman untuk truk). Default setiap boot");
}

static void setBench(bool on) {
  if (on == benchMode) { reportBench(); return; }

  if (!canReady) {                          // chip belum siap: cukup catat, bringUp() yang menerapkan mode ini
    benchMode = on;
    if (on) logMsg('W', "BENCH=1 AKTIF (menunggu init selesai): setelah READY chip NORMAL, memberi ACK di bus. JANGAN di truk");
    else    logMsg('I', "BENCH=0: LISTEN-ONLY (pasif, aman untuk truk)");
    return;
  }

  byte r = CAN0.setMode(on ? MCP_NORMAL : MCP_LISTENONLY);
  settleMs(5);
  bool ok = (r == CAN_OK);
  if (ok && rawOk) ok = ((mcpRead(REG_CANSTAT) >> 5) == (on ? 0 : 3));

  if (ok) {
    benchMode = on;
    mcpModify(REG_EFLG, 0xC0, 0x00);
    if (on) {
      logMsg('W', "BENCH=1 AKTIF: chip NORMAL (memberi ACK, aktif di bus). Hanya meja test, JANGAN di truk. Kirim 'bench 0' untuk kembali");
      if (!rawOk) logMsg('W', "BENCH=1: mode tidak diverifikasi lewat register (degraded)");
    } else {
      logMsg('I', "BENCH=0: LISTEN-ONLY (pasif, aman untuk truk)");
    }
    return;
  }

  // gagal: SELALU berakhir di sisi aman
  benchMode = false;
  CAN0.setMode(MCP_LISTENONLY);
  settleMs(5);
  if (on) {
    logMsg('E', "BENCH=0 (permintaan bench 1 GAGAL: chip tidak masuk NORMAL) -> dikembalikan ke LISTEN-ONLY");
  } else {
    logMsg('E', "BENCH=0 GAGAL masuk LISTEN-ONLY -> re-init chip (selalu LISTEN-ONLY)");
    canReady = false;
    lastInitTryMs = millis() - 2000UL;
  }
}

static void handleCmd(char *s) {
  while (*s == ' ') s++;
  if (*s == '/') s++;
  for (char *p = s; *p; p++) *p = (char)tolower((unsigned char)*p);
  size_t n = strlen(s);
  while (n && s[n - 1] == ' ') s[--n] = 0;

  if (strcmp(s, "bench 1") == 0)      setBench(true);
  else if (strcmp(s, "bench 0") == 0) setBench(false);
  else if (strcmp(s, "bench") == 0)   reportBench();
  else if (strncmp(s, "bench", 5) == 0) logMsg('W', "format: bench 0|1   (0 = LISTEN-ONLY aman untuk truk, 1 = NORMAL-ACK hanya meja test)");
  // baris lain diabaikan diam-diam (noise di jalur RX tidak boleh memicu apa pun)
}

static void pollCmd() {
  for (uint8_t guard = 0; guard < 32 && LOG_SERIAL.available(); guard++) {
    int ch = LOG_SERIAL.read();
    if (ch < 0) break;
    char c = (char)ch;
    if (c == '\r') continue;
    if (c == '\n') {
      cmdBuf[cmdLen] = '\0';
      if (cmdLen > 0) handleCmd(cmdBuf);
      cmdLen = 0;
    } else if (cmdLen < sizeof(cmdBuf) - 1) {
      cmdBuf[cmdLen++] = c;
    } else {
      cmdLen = 0;                           // terlalu panjang -> buang
    }
  }
}

// ---------- pemilihan cfg bitrate ----------
// Urutan kandidat: cfg terakhir yang terbukti hidup (atau cfg user), lalu sisanya; duplikat (speed,xtal) dilewati.
static uint8_t buildOrder(uint8_t *ord) {
  uint8_t n = 0;
  uint8_t first = (lastGood >= 0) ? (uint8_t)lastGood : 0;
  uint8_t cand[NCFG], m = 0;
  cand[m++] = first;
  for (uint8_t i = 0; i < NCFG; i++) if (i != first) cand[m++] = i;
  for (uint8_t k = 0; k < m; k++) {
    bool dup = false;
    for (uint8_t j = 0; j < n; j++)
      if (CFGS[ord[j]].speed == CFGS[cand[k]].speed && CFGS[ord[j]].xtal == CFGS[cand[k]].xtal) dup = true;
    if (!dup) ord[n++] = cand[k];
  }
  return n;
}

// Dipanggil tepat setelah CAN0.begin(): library bisa meninggalkan chip di mode apa saja.
// NORMAL = chip aktif di bus (ACK/error frame) -> di truk TIDAK boleh. Kalau bench=0: paksa LISTEN-ONLY & matikan scan.
static void guardModeAfterBegin(bool report) {
  if (!rawOk) return;
  uint8_t m = mcpRead(REG_CANSTAT) >> 5;
  if (report) {
    Line l; l.start('I', now64us());
    l.s("begin() meninggalkan chip di opmod=").u(m).s(" (0=NORMAL 2=LOOPBACK 3=LISTEN 4=CONFIG)");
    l.send();
  }
  if (m == 0 && !benchMode) {
    CAN0.setMode(MCP_LISTENONLY);
    autoOk = false;
    logMsg('W', "library begin() meninggalkan chip di NORMAL -> dipaksa LISTEN-ONLY, scan bitrate dimatikan demi keamanan bus");
  }
}

// Dengarkan bus (LISTEN-ONLY) selama windowMs. Valid bila cukup banyak frame extended DAN ID-nya berulang
// (bus J1939 sungguhan = ID yang sama muncul berulang; bitrate salah = sampah acak / tidak ada frame).
static bool scanListen(uint32_t windowMs, uint32_t &tot, uint32_t &rep) {
  uint32_t seen[32]; uint8_t ns = 0;
  tot = 0; rep = 0;
  uint32_t t0 = millis();
  while ((millis() - t0) < windowMs) {
    pumpTx(); ledUpdate(millis());
    for (uint8_t i = 0; i < 16 && CAN0.checkReceive() == CAN_MSGAVAIL; i++) {
      unsigned long id = 0; byte ext = 0, len = 0, buf[8];
      if (CAN0.readMsgBuf(&id, &ext, &len, buf) != CAN_OK) break;
      if (!ext) continue;
      id &= 0x1FFFFFFFUL;
      tot++;
      bool hit = false;
      for (uint8_t j = 0; j < ns; j++) if (seen[j] == id) { hit = true; break; }
      if (hit) rep++; else if (ns < 32) seen[ns++] = (uint32_t)id;
    }
  }
  return (tot >= SCAN_MIN_FRAMES) && (rep * 2 >= tot);
}

// ---------- bring-up + self-test ----------
static bool bringUp() {
  canReady = false;
  autoOk = AUTO_BITRATE;
  curCfg = (lastGood >= 0) ? (uint8_t)lastGood : 0;
  lastSvcUs = 0;

  crumb("stage 1/5: CAN0.begin() (library: reset + set bitrate)...");
  if (CAN0.begin(MCP_ANY, CFGS[curCfg].speed, CFGS[curCfg].xtal) != CAN_OK) {
    crumb("stage 1 GAGAL: CAN0.begin() mengembalikan error");
    logMsg('E', "CAN0.begin() GAGAL: MCP2515 tidak menjawab / kristal-bitrate tidak valid. Cek VCC=5V GND CS=PA4 SCK=PA5 MISO=PA6 MOSI=PA7");
    return false;
  }
  crumb("stage 1 OK: MCP2515 menjawab");

  crumb("stage 2/5: baca register langsung (raw SPI)...");
  rawOk = true;
  uint8_t cnf1 = mcpRead(REG_CNF1), cnf2 = mcpRead(REG_CNF2), cnf3 = mcpRead(REG_CNF3);
  if (cnf1 == cnf2 && cnf2 == cnf3 && (cnf1 == 0x00 || cnf1 == 0xFF)) {
    rawOk = false;
    autoOk = false;
    Line l; l.start('W', now64us());
    l.s("raw SPI tidak cocok dengan library (CNF1/2/3=0x").hx(cnf1, 2).s(") -> MODE DEGRADED: rec/tec/eflg/opmod tidak dipantau, scan bitrate mati");
    l.send();
    crumb("stage 2: raw SPI tidak cocok -> degraded (library tetap dipakai)");
  } else {
    Line l; l.start('I', now64us());
    l.s("MCP2515 init OK cfg=").s(CFGS[curCfg].txt).s(" CNF1=0x").hx(cnf1, 2)
     .s(" CNF2=0x").hx(cnf2, 2).s(" CNF3=0x").hx(cnf3, 2);
    l.send();
    guardModeAfterBegin(true);
    crumb("stage 2 OK");
  }

#if SELFTEST_LOOPBACK
  crumb("stage 3/5: self-test loopback internal (tidak menyentuh bus)...");
  CAN0.setMode(MCP_LOOPBACK);
  waitMs(5);
  byte txd[8] = {0xA5, 0x5A, 0x12, 0x34, 0x56, 0x78, 0x9A, 0xBC};
  byte txr = CAN0.sendMsgBuf(0x18FEF1AAUL, 1, 8, txd);
  bool pass = false;
  uint32_t t0 = millis();
  while ((millis() - t0) < 50) {
    if (CAN0.checkReceive() == CAN_MSGAVAIL) {
      unsigned long rid = 0; byte rext = 0, rlen = 0, rb[8] = {0};
      if (CAN0.readMsgBuf(&rid, &rext, &rlen, rb) == CAN_OK)
        pass = (rid == 0x18FEF1AAUL && rext == 1 && rlen == 8 && memcmp(rb, txd, 8) == 0);
      break;
    }
  }
  if (!pass) {
    crumb("stage 3 GAGAL: loopback tidak kembali");
    Line l; l.start('E', now64us());
    l.s("SELFTEST loopback GAGAL (send=").u(txr).s(") -> chip MCP2515 / SPI bermasalah (modul palsu atau sinyal SPI jelek)");
    l.send();
    return false;
  }
  logMsg('I', "SELFTEST loopback PASS: SPI + controller OK");
  crumb("stage 3 OK");
#endif

  // ---- stage 4: kunci bitrate (scan LISTEN-ONLY) lalu masuk mode kerja (LISTEN-ONLY, atau NORMAL kalau bench=1) ----
  crumb("stage 4/5: bitrate lock + set mode kerja...");
  uint8_t ord[NCFG];
  uint8_t norder = autoOk ? buildOrder(ord) : 1;
  if (!autoOk) ord[0] = curCfg;
  uint8_t target = curCfg;
  bool found = false;

  if (autoOk) {
    for (uint8_t k = 0; k < norder && autoOk; k++) {
      uint8_t idx = ord[k];
      if (idx != curCfg) {
        if (CAN0.begin(MCP_ANY, CFGS[idx].speed, CFGS[idx].xtal) != CAN_OK) { logMsg('W', "scan: begin() gagal untuk satu kandidat, dilewati"); continue; }
        curCfg = idx;
        guardModeAfterBegin(false);
        if (!autoOk) break;
      }
      CAN0.setMode(MCP_LISTENONLY);
      waitMs(5);
      if ((mcpRead(REG_CANSTAT) >> 5) != 3) { logMsg('W', "scan: gagal masuk LISTEN-ONLY, kandidat dilewati"); continue; }
      mcpModify(REG_EFLG, 0xC0, 0x00);
      uint32_t tot = 0, rep = 0;
      bool ok = scanListen(SCAN_WINDOW_MS, tot, rep);
      { Line l; l.start('I', now64us());
        l.s("scan cfg=").s(CFGS[idx].txt).s(" frames=").u(tot).s(" repeat=").u(rep).s(ok ? " -> BUS HIDUP" : " -> kosong/sampah");
        l.send(); }
      if (ok) { found = true; target = idx; break; }
    }
    if (!found) {
      target = (lastGood >= 0) ? (uint8_t)lastGood : 0;
      if (autoOk) logMsg('W', "scan: tidak ada bus aktif di semua cfg (kontak OFF / kabel?) -> tetap di cfg pilihan, scan ulang bila sepi lama");
    }
  }
  if (curCfg != target) {                       // pasang cfg final di chip
    if (CAN0.begin(MCP_ANY, CFGS[target].speed, CFGS[target].xtal) != CAN_OK) {
      logMsg('E', "begin() gagal saat memasang cfg final");
      return false;
    }
    curCfg = target;
    guardModeAfterBegin(false);
  }
  if (found) lastGood = (int8_t)target;

  CAN0.setMode(runMode());
  settleMs(5);
  if (rawOk) {
    uint8_t opmod = mcpRead(REG_CANSTAT) >> 5;
    if (opmod != runOpmod()) {
      crumb("stage 4 GAGAL: mode tidak sesuai");
      Line l; l.start('E', now64us());
      l.s("Gagal masuk mode ").s(runModeTxt()).s(", opmod=").u(opmod).s(" (harus ").u(runOpmod()).s(")");
      l.send();
      return false;
    }
  } else {
    logMsg('W', "mode tidak diverifikasi lewat register (degraded)");
  }
  mcpModify(REG_EFLG, 0xC0, 0x00);
  lastRxMs = millis();
  lastSvcUs = 0;
  canReady = true;
  crumb("stage 5/5: READY");
  { Line l; l.start('I', now64us());
    l.s("READY: cfg=").s(CFGS[curCfg].txt).s(found ? " (terkunci via scan)" : " (tanpa bukti bus)")
     .s(" mode ").s(runModeTxt()).s(rawOk ? " terverifikasi lewat register" : " (tanpa verifikasi register)");
    l.send(); }
  if (benchMode) logMsg('W', "BENCH=1 AKTIF: logger AKTIF di bus (memberi ACK). Hanya untuk meja test, JANGAN dipasang ke truk");
  return true;
}

static void periodic(uint32_t nowMs) {
  if ((nowMs - lastStatusMs) < STATUS_PERIOD_MS) return;
  lastStatusMs = nowMs;
  st.fps = st.framesSec; st.framesSec = 0;

  uint8_t opmod = rawOk ? (mcpRead(REG_CANSTAT) >> 5) : 255;
  uint8_t rec = mcpRead(REG_REC), tec = mcpRead(REG_TEC);
  uint32_t silent = nowMs - lastRxMs;

  { Line l; l.start('S', now64us());
    l.u(st.rxTotal).c(',').u(st.fps).c(',').u(st.ringDrop).c(',').u(st.ringHwm).c(',').u(st.hwOvr).c(',')
     .hx(lastEflg, 2).c(',').u(rec).c(',').u(tec).c(',').u(txHwm).c(',').u(st.loopMaxUs).c(',')
     .u(silent).c(',').u(st.lineDrop).c(',').u(opmod);
    l.send(); }

  if (rawOk && opmod != runOpmod()) {
    Line l; l.start('E', now64us());
    l.s("MCP2515 keluar dari mode ").s(runModeTxt()).s(" (opmod=").u(opmod).s(") / SPI putus -> re-init");
    l.send();
    canReady = false; lastInitTryMs = nowMs;
  }
  if (st.ringDrop != prevRingDrop) {
    Line l; l.start('W', now64us());
    l.s("DATA LOSS frame-ring penuh, total=").u(st.ringDrop).s(" (serial/host terlalu lambat)");
    l.send(); prevRingDrop = st.ringDrop;
  }
  if (st.hwOvr != prevHwOvr) {
    Line l; l.start('W', now64us());
    l.s("DATA LOSS MCP2515 RX overflow, kejadian=").u(st.hwOvr).s(" (jeda servis max=").u(st.loopMaxUs).s(" us)");
    l.send(); prevHwOvr = st.hwOvr;
  }
  if (st.lineDrop != prevLineDrop) {
    Line l; l.start('W', now64us());
    l.s("Baris info/status terbuang (tx buffer penuh), total=").u(st.lineDrop);
    l.send(); prevLineDrop = st.lineDrop;
  }
  if (st.loopMaxUs > 1000) {
    Line l; l.start('W', now64us());
    l.s("Jeda servis MCP2515 ").u(st.loopMaxUs).s(" us (>1000) -> risiko overflow saat bus padat");
    l.send();
  }
  if (txFallbackFlag) {
    logMsg('W', "availableForWrite() macet -> fallback write 64 byte/iterasi");
    txFallbackFlag = false;
  }
  if (canReady && silent > SILENT_WARN_MS && (nowMs - lastSilentWarnMs) >= 5000) {
    lastSilentWarnMs = nowMs;
    Line l; l.start('W', now64us());
    l.s("BUS SEPI ").u(silent).s(" ms opmod=").u(opmod).s(" eflg=0x").hx(lastEflg, 2).s(" rec=").u(rec).s(" tec=").u(tec)
     .s(" | cek: kontak ON, CAN-H/L, GND, jumper 120R HW-184 dilepas (bus truk sudah terminasi)");
    l.send();
  }
#if AUTO_BITRATE
  if (canReady && autoOk && silent > RESCAN_SILENT_MS) {
    logMsg('W', "bus sepi lama -> scan ulang bitrate");
    canReady = false;
    lastInitTryMs = nowMs - 2000UL;
  }
#endif
  st.loopMaxUs = 0;
}

// ---------- Arduino ----------
void setup() {
  benchMode = false;             // eksplisit: SETIAP boot mulai di LISTEN-ONLY (aman untuk truk)

  pinMode(LED_PIN, OUTPUT);
  digitalWrite(LED_PIN, LED_ACTIVE_LOW ? LOW : HIGH);  // LED nyala = firmware hidup
#if USE_INT_PIN
  pinMode(CAN_INT_PIN, INPUT_PULLUP);
#endif

  Serial.begin(115200);          // dipertahankan dari v2.4 (inisialisasi Serial utama); tidak dipakai untuk log
  Serial1.setRx(PA10);
  Serial1.setTx(PA9);
  LOG_SERIAL.begin(LOG_BAUD);
  delay(1500);                   // beri waktu MCP2515 & osilator stabil

  crumb("BOOT fw=" FW_VERSION " build=" __DATE__ " " __TIME__ " mode=" MODE_TXT_LISTEN " (bench=0)");

  { Line l; l.start('I', now64us());
    l.s("BOOT fw=" FW_VERSION " build=" __DATE__ " " __TIME__ " mode=" MODE_TXT_LISTEN " bench=0 cs=" CAN_CS_TXT " baud=").u(LOG_BAUD)
     .s(" serial_txbuf=").u(SERIAL_TX_BUFFER_SIZE).s(" auto=").u(AUTO_BITRATE)
     .s(" ring=").u(FRAME_RING_SIZE).s("/").u(TX_RING_SIZE);
    l.send(); }
  lastInitTryMs = millis();
  bringUp();
}

void loop() {
  uint32_t nowMs = millis();

  pollCmd();                     // perintah "bench 0|1" (RX Serial2); dibatasi 32 byte per putaran

  if (!canReady) {
    if ((nowMs - lastInitTryMs) >= 2000) { lastInitTryMs = nowMs; bringUp(); }
    pumpTx();
    ledUpdate(nowMs);
    return;
  }

  drainCan(32);
  pollHw(nowMs);
  formatFrames(8);
  pumpTx();
  drainCan(32);
  periodic(nowMs);
  ledUpdate(nowMs);
}
