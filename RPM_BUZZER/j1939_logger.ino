/*
 * J1939 PASSIVE LOGGER v2.0  --  STM32 (STM32duino) + HW-184 (MCP2515)
 * Target: Iveco Astra HD9, J1939 @ 250 kbps, mode LISTEN-ONLY (tidak pernah TX ke bus)
 *
 * Library : "MCP_CAN_lib" by coryjfowler
 * Wiring  : HW-184 VCC=5V, GND, CS=PA4, SCK=PA5, MISO=PA6, MOSI=PA7, INT=PB0 (opsional)
 * Arduino IDE (Blue Pill): Tools > USB support > "CDC (generic 'Serial' supersede U(S)ART)"
 *
 * PROTOKOL (tiap baris diakhiri  *CC  =  CRC-8 poly 0x07 dari semua karakter sebelum '*'):
 *   R,seq,t_us,ext,id_hex,pgn,sa,prio,dlc,data_hex     frame CAN mentah (SEMUA frame, std & ext)
 *   D,seq,t_us,sa,signal,value                         hasil decode J1939
 *   S,seq,t_us,rx_total,fps,ring_drop,ring_hwm,hw_ovr,eflg,rec,tec,tx_hwm,loop_max_us,silent_ms,line_drop,opmod
 *   I|W|E,seq,t_us,teks                                info / warning / error
 *
 *  - seq  : nomor urut global, naik 1 per baris. Kalau di PC ada lompatan = ada baris hilang.
 *  - t_us : mikrodetik sejak boot (64-bit, tidak wrap), diambil saat frame dibaca dari MCP2515.
 *
 * LED PC13:
 *   kedip cepat 5 Hz        = init/self-test GAGAL (lihat baris E di serial), auto-retry tiap 2 s
 *   blip pendek 1x / detik  = hidup, tapi belum ada frame CAN (bus sepi / bitrate salah / wiring)
 *   kedip 2 Hz              = frame CAN masuk normal
 *   nyala terus 5 s         = terjadi DATA LOSS (ring penuh / overflow MCP2515)
 */

#include <SPI.h>
#include <mcp_can.h>

#define FW_VERSION         "2.0"

// ===================== KONFIGURASI =====================
#define CAN_CS_PIN         PA4
#define CAN_CS_TXT         "PA4"
#define CAN_INT_PIN        PB0
#define USE_INT_PIN        0            // 0 = polling via SPI (paling aman, INT tidak perlu dikabel)
#define CAN_SPEED          CAN_250KBPS  // kalau bus sepi coba CAN_500KBPS
#define CAN_SPEED_TXT      "250kbps"
#define CAN_XTAL           MCP_8MHZ     // lihat tulisan di kristal modul: 8.000 atau 16.000
#define CAN_XTAL_TXT       "8MHz"
#define LOG_SERIAL         Serial
#define LOG_BAUD           921600       // dipakai kalau Serial = UART; diabaikan kalau USB CDC
#define REQUIRE_DTR        1            // USB CDC: tahan output sampai port dibuka host (buffer 4KB)
#define LED_PIN            PC13
#define LED_ACTIVE_LOW     1
#define SELFTEST_LOOPBACK  1            // tes chip MCP2515 secara internal (tidak menyentuh bus)
#define FRAME_RING_SIZE    256          // frame CAN yang ditahan di RAM
#define TX_RING_SIZE       4096         // byte serial yang ditahan di RAM
#define STATUS_PERIOD_MS   1000UL
#define SILENT_WARN_MS     3000UL
// =======================================================

MCP_CAN CAN0(CAN_CS_PIN);

// MCP2515 register
#define REG_TEC     0x1C
#define REG_REC     0x1D
#define REG_CNF3    0x28
#define REG_CNF2    0x29
#define REG_CNF1    0x2A
#define REG_EFLG    0x2D
#define REG_CANSTAT 0x0E
#define REG_CANCTRL 0x0F

static const char HEXCH[] = "0123456789ABCDEF";

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
static uint8_t  lastEflg = 0;

// ---------- util ----------
static uint64_t now64us() {  // micros() 32-bit wrap tiap ~71 menit -> jadikan 64-bit
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
  Line &fx(int64_t v, uint8_t dec) {  // fixed-point tanpa printf float
    if (v < 0) { c('-'); v = -v; }
    uint64_t p = 1;
    for (uint8_t i = 0; i < dec; i++) p *= 10;
    uint64_t w = (uint64_t)v / p, f = (uint64_t)v % p;
    u(w);
    if (dec) {
      c('.');
      char t[8];
      for (int8_t i = dec - 1; i >= 0; i--) { t[i] = '0' + (uint8_t)(f % 10); f /= 10; }
      for (uint8_t i = 0; i < dec; i++) c(t[i]);
    }
    return *this;
  }
  void start(char type, uint64_t t_us) { n = 0; c(type).c(',').u(seqNo).c(',').u(t_us).c(','); }
  bool send() {  // seq hanya naik kalau baris benar-benar masuk buffer -> tidak ada gap palsu
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
  if (!canReady)                                       on = ((now / 100) & 1);
  else if (st.lossEver && (now - st.lastLossMs) < 5000) on = true;
  else if (!everRx || (now - lastRxMs) > 2000)          on = ((now % 1000) < 60);
  else                                                  on = ((now / 250) & 1);
  digitalWrite(LED_PIN, LED_ACTIVE_LOW ? !on : on);
}

static void pumpTx() {
  uint32_t nowMs = millis();
  if (!txCount) { txLastProgressMs = nowMs; return; }
#if REQUIRE_DTR
  if (!LOG_SERIAL) { txLastProgressMs = nowMs; return; }  // host belum buka port
#endif
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

// ---------- akses register MCP2515 langsung (untuk diagnosa) ----------
static uint8_t mcpRead(uint8_t reg) {
  digitalWrite(CAN_CS_PIN, LOW);
  SPI.transfer(0x03); SPI.transfer(reg);
  uint8_t v = SPI.transfer(0x00);
  digitalWrite(CAN_CS_PIN, HIGH);
  return v;
}
static void mcpModify(uint8_t reg, uint8_t mask, uint8_t val) {
  digitalWrite(CAN_CS_PIN, LOW);
  SPI.transfer(0x05); SPI.transfer(reg); SPI.transfer(mask); SPI.transfer(val);
  digitalWrite(CAN_CS_PIN, HIGH);
}
static void mcpReset() {
  digitalWrite(CAN_CS_PIN, LOW);
  SPI.transfer(0xC0);
  digitalWrite(CAN_CS_PIN, HIGH);
}

// ---------- decode J1939 (SAE J1939-71) : semua integer, tanpa float ----------
static inline uint16_t u16(const uint8_t *d, uint8_t i) { return (uint16_t)(d[i] | (d[i + 1] << 8)); }
static inline uint32_t u32(const uint8_t *d, uint8_t i) {
  return (uint32_t)d[i] | ((uint32_t)d[i + 1] << 8) | ((uint32_t)d[i + 2] << 16) | ((uint32_t)d[i + 3] << 24);
}
static inline bool ok8(uint8_t v)   { return v < 0xFB; }        // 0xFB..0xFF = error / not available
static inline bool ok16(uint16_t v) { return v < 0xFB00; }
static inline bool ok32(uint32_t v) { return v < 0xFB000000UL; }

static void emitSig(uint64_t t, uint8_t sa, const char *name, int64_t v, uint8_t dec) {
  Line l; l.start('D', t); l.u(sa).c(',').s(name).c(',').fx(v, dec); l.send();
}

static void decodeJ1939(const Frame &f, uint32_t pgn, uint8_t sa) {
  const uint8_t *d = f.data;
  if (f.len < 8) return;
  uint64_t t = f.t_us;
  switch (pgn) {
    case 61444: {  // EEC1
      uint16_t rpm = u16(d, 3);
      if (ok16(rpm)) emitSig(t, sa, "engine_rpm", (int64_t)rpm * 125, 3);              // SPN190 0.125 rpm/bit
      if (ok8(d[2])) emitSig(t, sa, "actual_torque_pct", (int)d[2] - 125, 0);          // SPN513
      break;
    }
    case 61443:    // EEC2
      if (ok8(d[1])) emitSig(t, sa, "accel_pedal_pct", (int64_t)d[1] * 4, 1);          // SPN91 0.4 %/bit
      if (ok8(d[2])) emitSig(t, sa, "engine_load_pct", d[2], 0);                       // SPN92
      break;
    case 65265: {  // CCVS
      uint16_t v = u16(d, 1);
      if (ok16(v)) emitSig(t, sa, "vehicle_speed_kmh", ((int64_t)v * 125 + 16) / 32, 3);  // SPN84 1/256 km/h
      break;
    }
    case 65262: {  // ET1
      if (ok8(d[0])) emitSig(t, sa, "coolant_temp_c", (int)d[0] - 40, 0);              // SPN110
      if (ok8(d[1])) emitSig(t, sa, "fuel_temp_c", (int)d[1] - 40, 0);                 // SPN174
      uint16_t oil = u16(d, 2);
      if (ok16(oil)) emitSig(t, sa, "oil_temp_c", ((int64_t)oil * 25 + 4) / 8 - 27300, 2);  // SPN175 0.03125C, -273
      break;
    }
    case 65263:    // EFL/P1
      if (ok8(d[3])) emitSig(t, sa, "oil_pressure_kpa", (int64_t)d[3] * 4, 0);         // SPN100
      break;
    case 65266: {  // LFE
      uint16_t fr = u16(d, 0), fe = u16(d, 2);
      if (ok16(fr)) emitSig(t, sa, "fuel_rate_lph", (int64_t)fr * 5, 2);               // SPN183 0.05 L/h
      if (ok16(fe)) emitSig(t, sa, "fuel_econ_inst_kmpl", ((int64_t)fe * 125 + 32) / 64, 3);  // SPN184 1/512
      break;
    }
    case 65270:    // IC1
      if (ok8(d[1])) emitSig(t, sa, "boost_pressure_kpa", (int64_t)d[1] * 2, 0);       // SPN102
      if (ok8(d[2])) emitSig(t, sa, "intake_manifold_temp_c", (int)d[2] - 40, 0);      // SPN105
      break;
    case 65271: {  // VEP1
      uint16_t bv = u16(d, 4);
      if (ok16(bv)) emitSig(t, sa, "battery_v", (int64_t)bv * 5, 2);                   // SPN168 0.05 V/bit
      break;
    }
    case 65276:    // Dash display
      if (ok8(d[1])) emitSig(t, sa, "fuel_level_pct", (int64_t)d[1] * 4, 1);           // SPN96
      break;
    case 65253: {  // HOURS
      uint32_t h = u32(d, 0);
      if (ok32(h)) emitSig(t, sa, "engine_hours", (int64_t)h * 5, 2);                  // SPN247 0.05 h/bit
      break;
    }
    case 65248: {  // VD
      uint32_t km = u32(d, 4);
      if (ok32(km)) emitSig(t, sa, "total_distance_km", (int64_t)km * 125, 3);         // SPN245 0.125 km/bit
      break;
    }
    default: break;
  }
}

static void emitFrame(const Frame &f) {
  uint8_t prio = 0, sa = 0;
  uint32_t pgn = 0;
  if (f.ext) {
    prio = (f.id >> 26) & 0x07;
    sa   = f.id & 0xFF;
    uint8_t dp = (f.id >> 24) & 1, pf = (f.id >> 16) & 0xFF, ps = (f.id >> 8) & 0xFF;
    pgn = ((uint32_t)dp << 16) | ((uint32_t)pf << 8);
    if (pf >= 240) pgn |= ps;  // PDU2 broadcast; PDU1 -> PS = alamat tujuan
  }
  Line l; l.start('R', f.t_us);
  l.u(f.ext).c(',').hx(f.id, 8).c(',').u(pgn).c(',').u(sa).c(',').u(prio).c(',').u(f.len).c(',');
  for (uint8_t i = 0; i < f.len; i++) l.hx(f.data[i], 2);
  l.send();
  if (f.ext) decodeJ1939(f, pgn, sa);
}

// ---------- bring-up + self-test ----------
static bool bringUp() {
  canReady = false;
  mcpReset();
  waitMs(10);
  uint8_t cstat = mcpRead(REG_CANSTAT), cctrl = mcpRead(REG_CANCTRL);
  { Line l; l.start('I', now64us());
    l.s("SPI probe setelah RESET: CANSTAT=0x").hx(cstat, 2).s(" (harus 0x80) CANCTRL=0x").hx(cctrl, 2).s(" (harus 0x87)");
    l.send(); }
  if ((cstat & 0xE0) != 0x80) {
    logMsg('E', "SPI probe GAGAL: MCP2515 tidak menjawab (0x00/0xFF = tanpa respon). Cek VCC=5V GND CS=PA4 SCK=PA5 MISO=PA6 MOSI=PA7");
    return false;
  }
  if (CAN0.begin(MCP_ANY, CAN_SPEED, CAN_XTAL) != CAN_OK) {
    logMsg('E', "CAN0.begin() GAGAL: init MCP2515 / kombinasi bitrate-xtal tidak valid");
    return false;
  }
  { Line l; l.start('I', now64us());
    l.s("MCP2515 init OK cfg=" CAN_SPEED_TXT "/" CAN_XTAL_TXT " CNF1=0x").hx(mcpRead(REG_CNF1), 2)
     .s(" CNF2=0x").hx(mcpRead(REG_CNF2), 2).s(" CNF3=0x").hx(mcpRead(REG_CNF3), 2);
    l.send(); }

#if SELFTEST_LOOPBACK
  CAN0.setMode(MCP_LOOPBACK);  // internal saja, TXCAN tidak menyentuh bus
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
    Line l; l.start('E', now64us());
    l.s("SELFTEST loopback GAGAL (send=").u(txr).s(") -> chip MCP2515 / SPI bermasalah (modul palsu atau sinyal SPI jelek)");
    l.send();
    return false;
  }
  logMsg('I', "SELFTEST loopback PASS: SPI + controller OK");
#endif

  CAN0.setMode(MCP_LISTENONLY);
  waitMs(5);
  uint8_t opmod = mcpRead(REG_CANSTAT) >> 5;
  if (opmod != 3) {
    Line l; l.start('E', now64us());
    l.s("Gagal masuk LISTEN-ONLY, opmod=").u(opmod).s(" (harus 3)");
    l.send();
    return false;
  }
  for (uint8_t i = 0; i < 8 && CAN0.checkReceive() == CAN_MSGAVAIL; i++) {  // buang sisa self-test
    unsigned long rid; byte rext, rlen, rb[8];
    CAN0.readMsgBuf(&rid, &rext, &rlen, rb);
  }
  mcpModify(REG_EFLG, 0xC0, 0x00);
  lastRxMs = millis();
  everRx = false;
  canReady = true;
  logMsg('I', "READY: LISTEN-ONLY terverifikasi (opmod=3). Menunggu frame CAN...");
  return true;
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
  for (uint8_t i = 0; i < maxFrames; i++) {
    if (!msgPending()) break;
    unsigned long id = 0; byte ext = 0, len = 0, buf[8];
    if (CAN0.readMsgBuf(&id, &ext, &len, buf) != CAN_OK) break;
    uint64_t t = now64us();
    if (len > 8) len = 8;
    st.rxTotal++; st.framesSec++;
    lastRxMs = millis(); everRx = true;
    if (fCount >= FRAME_RING_SIZE) {  // ring penuh -> dihitung, bukan hilang diam-diam
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

static void formatFrames(uint8_t maxN) {
  while (maxN-- && fCount && txFree() >= 420) {  // backpressure: frame tetap aman di ring
    emitFrame(fring[fTail]);
    fTail = (fTail + 1) % FRAME_RING_SIZE;
    fCount--;
  }
}

static void pollHw(uint32_t nowMs) {
  static uint32_t last = 0;
  if ((nowMs - last) < 20) return;
  last = nowMs;
  uint8_t e = mcpRead(REG_EFLG);
  lastEflg = e;
  if (e & 0xC0) {  // RX0OVR / RX1OVR : MCP2515 kehilangan frame
    st.hwOvr++; st.lossEver = true; st.lastLossMs = nowMs;
    mcpModify(REG_EFLG, 0xC0, 0x00);
  }
}

static void periodic(uint32_t nowMs) {
  if ((nowMs - lastStatusMs) < STATUS_PERIOD_MS) return;
  lastStatusMs = nowMs;
  st.fps = st.framesSec; st.framesSec = 0;

  uint8_t opmod = mcpRead(REG_CANSTAT) >> 5;
  uint8_t rec = mcpRead(REG_REC), tec = mcpRead(REG_TEC);
  uint32_t silent = nowMs - lastRxMs;

  { Line l; l.start('S', now64us());
    l.u(st.rxTotal).c(',').u(st.fps).c(',').u(st.ringDrop).c(',').u(st.ringHwm).c(',').u(st.hwOvr).c(',')
     .hx(lastEflg, 2).c(',').u(rec).c(',').u(tec).c(',').u(txHwm).c(',').u(st.loopMaxUs).c(',')
     .u(silent).c(',').u(st.lineDrop).c(',').u(opmod);
    l.send(); }

  if (opmod != 3) {
    Line l; l.start('E', now64us());
    l.s("MCP2515 keluar dari LISTEN-ONLY (opmod=").u(opmod).s(") / SPI putus -> re-init");
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
    l.s("DATA LOSS MCP2515 RX overflow, kejadian=").u(st.hwOvr).s(" (loop_max_us=").u(st.loopMaxUs).s(")");
    l.send(); prevHwOvr = st.hwOvr;
  }
  if (st.lineDrop != prevLineDrop) {
    Line l; l.start('W', now64us());
    l.s("Baris info/status terbuang (tx buffer penuh), total=").u(st.lineDrop);
    l.send(); prevLineDrop = st.lineDrop;
  }
  if (st.loopMaxUs > 1500) {
    Line l; l.start('W', now64us());
    l.s("Loop stall ").u(st.loopMaxUs).s(" us (>1500) -> risiko overflow MCP2515 saat bus padat");
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
     .s(" | cek: kontak ON, CAN-H/L, bitrate 250/500k, xtal 8/16MHz, terminasi");
    l.send();
  }
  st.loopMaxUs = 0;
}

// ---------- Arduino ----------
void setup() {
  pinMode(LED_PIN, OUTPUT);
  digitalWrite(LED_PIN, LED_ACTIVE_LOW ? LOW : HIGH);
  pinMode(CAN_CS_PIN, OUTPUT);
  digitalWrite(CAN_CS_PIN, HIGH);
#if USE_INT_PIN
  pinMode(CAN_INT_PIN, INPUT_PULLUP);
#endif
  LOG_SERIAL.begin(LOG_BAUD);
  SPI.begin();

  { Line l; l.start('I', now64us());
    l.s("BOOT fw=" FW_VERSION " build=" __DATE__ " " __TIME__ " can=" CAN_SPEED_TXT "/" CAN_XTAL_TXT
        " cs=" CAN_CS_TXT " ring=").u(FRAME_RING_SIZE).s("/").u(TX_RING_SIZE);
    l.send(); }
  lastInitTryMs = millis();
  bringUp();
}

void loop() {
  uint32_t t0 = micros();
  uint32_t nowMs = millis();

  if (!canReady) {
    if ((nowMs - lastInitTryMs) >= 2000) { lastInitTryMs = nowMs; bringUp(); }
    pumpTx();
    ledUpdate(nowMs);
    return;
  }

  drainCan(32);
  pollHw(nowMs);
  formatFrames(16);
  pumpTx();
  drainCan(32);  // sekali lagi: serial pump bisa makan waktu
  periodic(nowMs);
  ledUpdate(nowMs);

  uint32_t dt = micros() - t0;
  if (dt > st.loopMaxUs) st.loopMaxUs = dt;
}
