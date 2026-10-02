/*
 * J1939 FREEZE BISECT  --  cari fitur logger mana yang bikin freeze
 *
 * Mulai dari baseline = hw_check (CAN + print teks, terbukti jalan), lalu fitur logger dinyalakan SATU PER SATU
 * tiap STEP_MS (12 detik). Sebelum tiap fitur dinyalakan dicetak banner "[STEP n] ...".
 * Output: Serial2 @9600 (jalur yang sudah terbukti kebaca di terminal lu). Nyalakan sender (10 fps) selama tes.
 *
 * CARA BACA:
 *   - Tiap detik ada baris [HB] step=.. loops=.. frames=.. free=.. Kalau [HB] BERHENTI muncul = FREEZE.
 *   - STEP terakhir yang tercetak sebelum berhenti = fitur penyebab (yang ada tanda <== TERSANGKA).
 *   - Dari STEP 6 watchdog aktif: kalau freeze, board reset sendiri ~8 detik kemudian dan muncul [BOOT] lagi.
 *     Step terakhir di [HB] sebelum [BOOT] itu = penyebabnya.
 *   - free=... itu sisa RAM (stack vs heap). Kalau kecil (<1500) atau turun terus = masalah RAM, bukan fitur.
 *   - Lolos semua: muncul "[DONE]" dan [HB] terus jalan -> logger v2.5 seharusnya tidak freeze lagi.
 */

#include <SPI.h>
#include <mcp_can.h>
#include <IWatchdog.h>

// ================= KONFIG (sama dengan hw_check) =================
#define CAN_CS_PIN       PA4
#define CAN_CRYSTAL      MCP_8MHZ
#define CAN_SPEED        CAN_250KBPS
#define BENCH_ACK_MODE   1
#define LOG_SERIAL       Serial2
#define LOG_BAUD         9600
#ifndef STEP_MS
#define STEP_MS          12000UL
#endif
#define LAST_STEP        8
#define FRAME_RING_SIZE  256        // sama dengan logger
#define TX_RING_SIZE     4096       // sama dengan logger
#define USE_FREERAM      1          // 0 kalau link error di sbrk
#ifndef LED_PIN
#ifdef LED_BUILTIN
#define LED_PIN LED_BUILTIN
#else
#define LED_PIN PC13
#endif
#endif
// =================================================================

MCP_CAN CAN0(CAN_CS_PIN);

// PENTING: semua tipe (struct) harus didefinisikan SEBELUM fungsi pertama di file ini. Arduino IDE menyisipkan prototipe
// otomatis sebelum fungsi pertama, jadi fungsi yang memakai "const Frame &" error kalau Frame didefinisikan sesudahnya.
struct Frame { uint64_t t_us; uint32_t id; uint8_t ext; uint8_t len; uint8_t data[8]; };

#if USE_FREERAM
extern "C" char *sbrk(int incr);
static int freeRam() { char top; return (int)(&top - reinterpret_cast<char *>(sbrk(0))); }
#else
static int freeRam() { return -1; }
#endif

// ---- RAM statis sebesar logger (6 KB + 4 KB) ----
static Frame   fring[FRAME_RING_SIZE];
static uint8_t txbuf[TX_RING_SIZE];
static uint16_t txHead = 0, txTail = 0, txCount = 0;
static uint32_t seqNo = 0, lineDrop = 0, sink = 0;
static const char HEXCH[] = "0123456789ABCDEF";

// ---- fitur yang dinyalakan bertahap ----
static bool fLine = false, fRing = false, fRaw = false, fArg4 = false, fDecode = false, fWdt = false;
static uint8_t step = 0;
static uint32_t stepStart = 0;

static uint32_t frames = 0, secFrames = 0, loopCount = 0, loopMaxUs = 0, lastHb = 0, rawReads = 0;
static uint32_t lastRpmX10 = 0; static bool haveRpm = false;
static uint8_t lastEflg = 0, lastRec = 0, lastTec = 0;
static bool canOk = false;

static uint64_t now64us() {
  static uint32_t last = 0, hi = 0;
  uint32_t m = micros();
  if (m < last) hi++;
  last = m;
  return ((uint64_t)hi << 32) | m;
}
static uint8_t crc8(const char *s, uint16_t n) {
  uint8_t c = 0;
  while (n--) { c ^= (uint8_t)*s++; for (uint8_t i = 0; i < 8; i++) c = (c & 0x80) ? (uint8_t)((c << 1) ^ 0x07) : (uint8_t)(c << 1); }
  return c;
}
static inline uint16_t txFree() { return TX_RING_SIZE - txCount; }
static void txPush(const char *b, uint16_t n) {
  for (uint16_t i = 0; i < n; i++) { txbuf[txHead] = (uint8_t)b[i]; txHead = (txHead + 1) % TX_RING_SIZE; }
  txCount += n;
}

struct Line {
  char b[200]; uint16_t n;
  Line &c(char ch) { if (n < 184) b[n++] = ch; return *this; }
  Line &s(const char *p) { while (*p && n < 184) b[n++] = *p++; return *this; }
  Line &u(uint64_t v) { char t[21]; uint8_t k = 0; do { t[k++] = '0' + (uint8_t)(v % 10); v /= 10; } while (v); while (k && n < 184) b[n++] = t[--k]; return *this; }
  Line &hx(uint32_t v, uint8_t d) { for (int8_t i = (d - 1) * 4; i >= 0; i -= 4) c(HEXCH[(v >> i) & 0xF]); return *this; }
  Line &fx(int64_t v, uint8_t dec) {
    if (v < 0) { c('-'); v = -v; }
    uint64_t p = 1; for (uint8_t i = 0; i < dec; i++) p *= 10;
    uint64_t w = (uint64_t)v / p, f = (uint64_t)v % p; u(w);
    if (dec) { c('.'); char t[8]; for (int8_t i = dec - 1; i >= 0; i--) { t[i] = '0' + (uint8_t)(f % 10); f /= 10; } for (uint8_t i = 0; i < dec; i++) c(t[i]); }
    return *this;
  }
  void start(char type, uint64_t t_us) { n = 0; c(type).c(',').u(seqNo).c(',').u(t_us).c(','); }
  void send() {
    uint8_t ck = crc8(b, n);
    b[n++] = '*'; b[n++] = HEXCH[ck >> 4]; b[n++] = HEXCH[ck & 15]; b[n++] = '\n';
    if (!fRing) { sink += n; return; }                  // belum ke jalur output: cuma dihitung
    if (txFree() < n) { lineDrop++; return; }
    txPush(b, n); seqNo++;
  }
};

static void pumpTx() {                                  // sama dengan logger v2.5
  if (!txCount) return;
  int room = LOG_SERIAL.availableForWrite();
  if (room <= 0) return;
  uint16_t n = ((uint16_t)room > txCount) ? txCount : (uint16_t)room;
  uint16_t contiguous = TX_RING_SIZE - txTail;
  if (n > contiguous) n = contiguous;
  size_t w = LOG_SERIAL.write(&txbuf[txTail], n);
  if (w) { txTail = (txTail + w) % TX_RING_SIZE; txCount -= w; }
}
static void drainTx(uint32_t maxMs) {
  uint32_t t0 = millis();
  while (txCount && (millis() - t0) < maxMs) { if (fWdt) IWatchdog.reload(); pumpTx(); }
}
static void say(const char *s) { drainTx(2000); LOG_SERIAL.println(s); }   // kuras ring dulu supaya tidak menyelip di tengah baris

// ---- akses register SPI mentah (tersangka #1) ----
static uint8_t mcpRead(uint8_t reg) {
  digitalWrite(CAN_CS_PIN, LOW);
  SPI.transfer(0x03); SPI.transfer(reg);
  uint8_t v = SPI.transfer(0x00);
  digitalWrite(CAN_CS_PIN, HIGH);
  return v;
}
static void pollRaw(uint32_t now) {
  static uint32_t last = 0;
  if ((now - last) < 20) return;
  last = now;
  lastEflg = mcpRead(0x2D); lastRec = mcpRead(0x1D); lastTec = mcpRead(0x1C); rawReads++;
}

// ---- self-test loopback (tersangka #2) ----
static void doLoopback() {
  CAN0.setMode(MCP_LOOPBACK);
  delay(5);
  byte txd[8] = {0xA5, 0x5A, 0x12, 0x34, 0x56, 0x78, 0x9A, 0xBC};
  byte sr = CAN0.sendMsgBuf(0x18FEF1AAUL, 1, 8, txd);
  bool pass = false;
  uint32_t t0 = millis();
  while ((millis() - t0) < 100) {
    if (CAN0.checkReceive() == CAN_MSGAVAIL) {
      unsigned long rid = 0; byte rlen = 0, rb[8] = {0};
      if (CAN0.readMsgBuf(&rid, &rlen, rb) == CAN_OK) pass = ((rid & 0x1FFFFFFFUL) == 0x18FEF1AAUL && rlen == 8 && rb[0] == 0xA5);
      break;
    }
  }
#if BENCH_ACK_MODE
  CAN0.setMode(MCP_NORMAL);
#else
  CAN0.setMode(MCP_LISTENONLY);
#endif
  delay(5);
  LOG_SERIAL.print(sr == 0 ? "[LOOPBACK] send ok, " : "[LOOPBACK] send gagal, ");
  LOG_SERIAL.println(pass ? "terima PASS" : "terima GAGAL");
}

// ---- decode (tersangka #4: kode lebih besar, aritmetika 64-bit) ----
static void emitSig(uint64_t t, uint8_t sa, const char *name, int64_t v, uint8_t dec) {
  Line l; l.start('D', t); l.u(sa).c(',').s(name).c(',').fx(v, dec); l.send();
}
static void decodeJ1939(const Frame &f, uint32_t pgn, uint8_t sa) {
  const uint8_t *d = f.data;
  if (f.len < 8) return;
  switch (pgn) {
    case 61444: { uint16_t rpm = d[3] | (d[4] << 8); if (rpm < 0xFB00) emitSig(f.t_us, sa, "engine_rpm", (int64_t)rpm * 125, 3); break; }
    case 65265: { uint16_t v = d[1] | (d[2] << 8); if (v < 0xFB00) emitSig(f.t_us, sa, "vehicle_speed_kmh", ((int64_t)v * 125 + 16) / 32, 3); break; }
    case 65262: { if (d[0] < 0xFB) emitSig(f.t_us, sa, "coolant_temp_c", (int)d[0] - 40, 0);
                  uint16_t o = d[2] | (d[3] << 8); if (o < 0xFB00) emitSig(f.t_us, sa, "oil_temp_c", ((int64_t)o * 25 + 4) / 8 - 27300, 2); break; }
    default: break;
  }
}

// ---- satu frame masuk ----
static void onFrame(unsigned long id, bool ext, byte len, byte *d) {
  frames++; secFrames++;
  uint32_t pgn = 0; uint8_t sa = 0, prio = 0;
  if (ext) {
    sa = id & 0xFF; prio = (id >> 26) & 7;
    uint8_t dp = (id >> 24) & 1, pf = (id >> 16) & 0xFF, ps = (id >> 8) & 0xFF;
    pgn = ((uint32_t)dp << 16) | ((uint32_t)pf << 8);
    if (pf >= 240) pgn |= ps;
  }
  if (ext && pgn == 61444UL && len >= 5) {                  // RPM untuk heartbeat (seperti hw_check)
    uint16_t raw = d[3] | ((uint16_t)d[4] << 8);
    if (raw != 0xFFFF) { lastRpmX10 = ((uint32_t)raw * 10UL) / 8UL; haveRpm = true; }
  }
  if (fLine) {                                              // STEP 2+: bentuk baris R + CRC + timestamp 64-bit
    Frame f; f.t_us = now64us(); f.id = id; f.ext = ext; f.len = len; memcpy(f.data, d, len);
    Line l; l.start('R', f.t_us);
    l.u(ext).c(',').hx(id, 8).c(',').u(pgn).c(',').u(sa).c(',').u(prio).c(',').u(len).c(',');
    for (uint8_t i = 0; i < len; i++) l.hx(d[i], 2);
    l.send();                                               // STEP 3+: masuk TX ring -> keluar serial
    if (fDecode && ext) decodeJ1939(f, pgn, sa);            // STEP 8
  }
}

static bool readFrame(unsigned long &id, bool &ext, byte &len, byte *buf) {
  if (!fArg4) {                                             // 3-argumen (seperti hw_check)
    unsigned long raw = 0;
    if (CAN0.readMsgBuf(&raw, &len, buf) != CAN_OK) return false;
    ext = (raw & 0x80000000UL) != 0; id = raw & 0x1FFFFFFFUL; return true;
  }
  byte e = 0; unsigned long i = 0;                          // STEP 7: 4-argumen (tersangka #3)
  if (CAN0.readMsgBuf(&i, &e, &len, buf) != CAN_OK) return false;
  id = i & 0x1FFFFFFFUL; ext = (e != 0); return true;
}

static bool touchRam() {
  memset(fring, 0xA5, sizeof(fring)); memset(txbuf, 0x5A, sizeof(txbuf));
  bool ok = true;
  const uint8_t *a = (const uint8_t *)fring;
  for (size_t i = 0; i < sizeof(fring); i++) if (a[i] != 0xA5) { ok = false; break; }
  for (size_t i = 0; i < sizeof(txbuf); i++) if (txbuf[i] != 0x5A) { ok = false; break; }
  memset(fring, 0, sizeof(fring)); memset(txbuf, 0, sizeof(txbuf));
  return ok;
}

static void enterStep(uint8_t n) {
  step = n; stepStart = millis();
  switch (n) {
    case 0: say("[STEP 0] baseline = hw_check (CAN + print teks)"); break;
    case 1: say("[STEP 1] + sentuh RAM statis 10 KB (ring frame 6KB + ring TX 4KB)");
            say(touchRam() ? "[STEP 1] RAM OK" : "[STEP 1] RAM RUSAK/tertimpa  <== MASALAH RAM"); break;
    case 2: say("[STEP 2] + Line builder + CRC8 + timestamp 64-bit (belum dikirim)"); fLine = true; break;
    case 3: say("[STEP 3] + jalur output logger: TX ring + pump -> baris R,...*CC keluar serial"); fRing = true; break;
    case 4: say("[STEP 4] + baca register SPI mentah tiap 20 ms  <== TERSANGKA"); fRaw = true; break;
    case 5: say("[STEP 5] + self-test loopback (ganti mode lalu kembali)  <== TERSANGKA"); doLoopback(); break;
    case 6: say("[STEP 6] + watchdog 8 s + reload  (freeze setelah ini = reset sendiri ~8 s)"); IWatchdog.begin(8000000); fWdt = true; break;
    case 7: say("[STEP 7] + readMsgBuf 4-argumen  <== TERSANGKA"); fArg4 = true; break;
    case 8: say("[STEP 8] + decode J1939 di MCU (aritmetika 64-bit)"); fDecode = true; break;
  }
}

static void heartbeat(uint32_t now) {
  drainTx(2000);
  LOG_SERIAL.print("[HB] step="); LOG_SERIAL.print(step);
  LOG_SERIAL.print(" up="); LOG_SERIAL.print(now / 1000);
  LOG_SERIAL.print("s loops="); LOG_SERIAL.print(loopCount);
  LOG_SERIAL.print(" maxloop_us="); LOG_SERIAL.print(loopMaxUs);
  LOG_SERIAL.print(" frames="); LOG_SERIAL.print(frames);
  LOG_SERIAL.print(" fps="); LOG_SERIAL.print(secFrames);
  LOG_SERIAL.print(" free="); LOG_SERIAL.print(freeRam());
  if (fRaw) { LOG_SERIAL.print(" eflg=0x"); LOG_SERIAL.print(lastEflg, HEX); LOG_SERIAL.print(" rec="); LOG_SERIAL.print(lastRec); LOG_SERIAL.print(" tec="); LOG_SERIAL.print(lastTec); }
  if (fRing) { LOG_SERIAL.print(" lineDrop="); LOG_SERIAL.print(lineDrop); }
  LOG_SERIAL.print(" rpm=");
  if (haveRpm) { LOG_SERIAL.print(lastRpmX10 / 10); LOG_SERIAL.print('.'); LOG_SERIAL.print(lastRpmX10 % 10); } else LOG_SERIAL.print('-');
  LOG_SERIAL.println();
  secFrames = 0; loopCount = 0; loopMaxUs = 0;
}

static bool initCan() {
  say("[INIT] CAN0.begin(250kbps) ...");
  if (CAN0.begin(MCP_ANY, CAN_SPEED, CAN_CRYSTAL) != CAN_OK) { say("[INIT] GAGAL - cek wiring/crystal/power HW-184"); return false; }
#if BENCH_ACK_MODE
  CAN0.setMode(MCP_NORMAL);  say("[INIT] OK, mode NORMAL (meja test)");
#else
  CAN0.setMode(MCP_LISTENONLY); say("[INIT] OK, mode LISTEN-ONLY");
#endif
  return true;
}

void setup() {
  pinMode(LED_PIN, OUTPUT);
  LOG_SERIAL.begin(LOG_BAUD);
  delay(1500);
  LOG_SERIAL.println();
  LOG_SERIAL.println("=== J1939 FREEZE BISECT ===");
  if (IWatchdog.isReset(true)) LOG_SERIAL.println("[BOOT] reset karena WATCHDOG -> ada freeze di step terakhir yang tercetak di [HB] sebelum ini");
  else LOG_SERIAL.println("[BOOT] normal");
  LOG_SERIAL.print("[BOOT] RAM bebas awal = "); LOG_SERIAL.print(freeRam()); LOG_SERIAL.println(" byte (stack+heap). <1500 = bahaya");
  canOk = initCan();
  enterStep(0);
}

void loop() {
  if (fWdt) IWatchdog.reload();
  uint32_t t0 = micros(), now = millis();
  loopCount++;

  if (!canOk) {                                             // retry init seperti hw_check
    static uint32_t lastTry = 0;
    if ((now - lastTry) >= 3000) { lastTry = now; canOk = initCan(); if (canOk) stepStart = millis(); }
    return;
  }

  if (step < LAST_STEP && (now - stepStart) >= STEP_MS) enterStep(step + 1);
  else if (step == LAST_STEP && (now - stepStart) >= STEP_MS) { say("[DONE] semua langkah lolos tanpa freeze"); step = LAST_STEP + 1; }

  for (byte i = 0; i < 32 && CAN0.checkReceive() == CAN_MSGAVAIL; i++) {
    unsigned long id = 0; bool ext = false; byte len = 0, buf[8] = {0};
    if (!readFrame(id, ext, len, buf)) break;
    if (len > 8) len = 8;
    onFrame(id, ext, len, buf);
  }
  if (fRing) pumpTx();
  if (fRaw) pollRaw(now);

  if ((now - lastHb) >= 1000) { lastHb = now; digitalWrite(LED_PIN, !digitalRead(LED_PIN)); heartbeat(now); }

  uint32_t dt = micros() - t0;
  if (dt > loopMaxUs) loopMaxUs = dt;
}
