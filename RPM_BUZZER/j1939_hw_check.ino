/*
 * J1939 HW CHECK v2 (sisi logger/receiver)  --  STM32 + HW-184 (MCP2515)
 *
 * Dibuat meniru kode receiver yang SUDAH TERBUKTI JALAN:
 *   begin(MCP_ANY) -> setMode -> checkReceive() -> readMsgBuf(&id,&len,buf) 3-argumen,
 *   Serial.begin(115200) tanpa menunggu/cek DTR, Serial2 (Bluetooth) 9600.
 *
 * OUTPUT KE SEMUA JALUR SEKALIGUS supaya pasti ada yang terlihat:
 *   - Serial                     (USB CDC kalau "supersede", atau USART1 PA9/PA10 kalau tidak)
 *   - SerialUSB                  (otomatis kalau menu USB = "CDC (no generic 'Serial')")
 *   - Serial2 @9600 (Bluetooth)  (ringkas saja: INIT / RPM / STAT / DIAG; baris [RX] mentah hanya ke USB)
 *
 * INDIKATOR TANPA SERIAL (LED aktif LOW seperti kode lu):
 *   PC13 (onboard)  kedip 1 Hz selalu         = firmware HIDUP
 *   PB6 merah       kedip cepat               = init CAN GAGAL
 *   PB5 hijau       kedip 1 Hz                = CAN siap, belum ada frame (standby)
 *   PB5 hijau       nyala terus               = frame CAN masuk
 *
 * MODE: BENCH_ACK_MODE 1 = NORMAL (ikut ACK, untuk meja test dgn sender). 0 = LISTEN-ONLY (pasif, untuk truk).
 */

#include <SPI.h>
#include <mcp_can.h>

// ================= KONFIG =================
#define CAN_CS_PIN      PA4
#define CAN_CRYSTAL     MCP_8MHZ      // cek tulisan di kristal modul: 8.000 / 16.000
#define CAN_SPEED       CAN_250KBPS
#define BENCH_ACK_MODE  1
#define USE_SERIAL2     1             // mirror ringkas ke Bluetooth (PA2/PA3 @9600)
#define PIN_LED_RED     PB6
#define PIN_LED_GREEN   PB5
#define PIN_ONBOARD     PC13
#define PRINT_RAW       1             // baris [RX] mentah (dibatasi, hanya ke USB/Serial)
#define MAX_RAW_PER_SEC 20
// ==========================================

MCP_CAN CAN0(CAN_CS_PIN);

// "no generic Serial": Serial = UART, USB ada di SerialUSB
#if defined(USBD_USE_CDC) && defined(DISABLE_GENERIC_SERIALUSB)
  #define HAVE_SERIALUSB 1
#else
  #define HAVE_SERIALUSB 0
#endif

class Mux : public Print {
 public:
  using Print::write;
  bool toBt = true;  // false = baris ini tidak dikirim ke Bluetooth (9600 baud terlalu lambat untuk banyak baris)
  size_t write(uint8_t c) override { return write(&c, 1); }
  size_t write(const uint8_t *b, size_t n) override {
    Serial.write(b, n);
#if HAVE_SERIALUSB
    SerialUSB.write(b, n);
#endif
#if USE_SERIAL2
    if (toBt) Serial2.write(b, n);
#endif
    return n;
  }
};
static Mux out;

static bool     canOk = false;
static uint32_t totalFrames = 0, eec1Frames = 0, secFrames = 0, rawPrinted = 0;
static uint32_t lastReport = 0, lastRetry = 0, lastRxMs = 0, readySince = 0, lastRpmPrint = 0, lastRpmMs = 0;
static uint32_t lastRpmX10 = 0;
static bool     haveRpm = false;

static void say(const char *s) { out.toBt = true; out.println(s); }

// LED aktif LOW: LOW = nyala
static void indicators(bool red, bool green) {
  digitalWrite(PIN_LED_RED, !red);
  digitalWrite(PIN_LED_GREEN, !green);
}

static void printX10(uint32_t x10) {  // tanpa float: 14805 -> "1480.5"
  out.print(x10 / 10); out.print('.'); out.print(x10 % 10);
}

static bool initCan() {
  say("[INIT] CAN0.begin(250kbps) ...");
  if (CAN0.begin(MCP_ANY, CAN_SPEED, CAN_CRYSTAL) == CAN_OK) {
    say("[INIT] MCP2515 init OK");
  } else {
    say("[INIT] MCP2515 init GAGAL - cek wiring/crystal/power HW-184");
    say("       VCC=5V, GND, CS=PA4, SCK=PA5, MISO=PA6, MOSI=PA7, kristal 8/16 MHz (CAN_CRYSTAL)");
    return false;
  }
#if BENCH_ACK_MODE
  CAN0.setMode(MCP_NORMAL);
  say("[INIT] mode NORMAL (ikut ACK) - MEJA TEST, jangan dipasang ke truk");
#else
  CAN0.setMode(MCP_LISTENONLY);
  say("[INIT] mode LISTEN-ONLY (pasif)");
#endif
  say("[INIT] Siap, nunggu frame J1939 ...");
  readySince = millis();
  lastRxMs = readySince;
  return true;
}

static void handleFrame(unsigned long rxId, unsigned char len, unsigned char *d) {
  uint32_t now = millis();
  bool ext = (rxId & 0x80000000UL) != 0;
  unsigned long id = rxId & 0x1FFFFFFFUL;
  totalFrames++; secFrames++; lastRxMs = now;

  uint32_t pgn = 0; uint8_t sa = 0;
  if (ext) {
    sa = id & 0xFF;
    uint8_t dp = (id >> 24) & 1, pf = (id >> 16) & 0xFF, ps = (id >> 8) & 0xFF;
    pgn = ((uint32_t)dp << 16) | ((uint32_t)pf << 8);
    if (pf >= 240) pgn |= ps;
  }

#if PRINT_RAW
  if (rawPrinted < MAX_RAW_PER_SEC) {
    rawPrinted++;
    out.toBt = false;
    out.print("[RX] id=0x"); out.print(id, HEX);
    out.print(ext ? " EXT" : " STD");
    out.print(" PGN="); out.print(pgn);
    out.print(" SA="); out.print(sa);
    out.print(" len="); out.print(len);
    out.print(" data=");
    for (unsigned char i = 0; i < len; i++) {
      if (d[i] < 16) out.print('0');
      out.print(d[i], HEX); out.print(' ');
    }
    out.println();
    out.toBt = true;
  }
#endif

  if (ext && pgn == 61444UL && len >= 5) {                 // EEC1
    uint16_t raw = d[3] | ((uint16_t)d[4] << 8);           // SPN190 0.125 rpm/bit
    if (raw != 0xFFFF) {
      lastRpmX10 = ((uint32_t)raw * 10UL) / 8UL;
      haveRpm = true; eec1Frames++; lastRpmMs = now;
      if ((now - lastRpmPrint) >= 200) {
        lastRpmPrint = now;
        out.toBt = true;
        out.print("[RPM] "); printX10(lastRpmX10); out.print(" rpm  SA=0x"); out.println(sa, HEX);
      }
    }
  }
}

static void report(uint32_t now) {
  out.toBt = true;
  out.print("[STAT] up="); out.print(now / 1000);
  out.print("s init="); out.print(canOk ? "OK" : "GAGAL");
  out.print(" frames="); out.print(totalFrames);
  out.print(" fps="); out.print(secFrames);
  out.print(" EEC1="); out.print(eec1Frames);
  out.print(" rpm=");
  if (haveRpm) printX10(lastRpmX10); else out.print('-');
  out.println();

  uint32_t sinceReady = now - readySince;
  if (canOk && totalFrames == 0 && sinceReady > 3000) {
    say("[DIAG] CAN init OK tapi BELUM ADA FRAME. Cek: sender nyala? | logger mode NORMAL (BENCH_ACK_MODE 1)?");
    say("       CAN-H/CAN-L tidak tertukar + GND nyambung | terminasi 120 ohm (jumper) | bitrate & xtal sama");
  } else if (canOk && totalFrames && secFrames == 0 && (now - lastRxMs) > 3000) {
    say("[DIAG] Frame sebelumnya masuk, sekarang berhenti (sender mati / kabel lepas).");
  } else if (canOk && totalFrames && eec1Frames == 0 && sinceReady > 3000) {
    say("[DIAG] Frame masuk tapi belum ada EEC1 (PGN 61444).");
  } else if (secFrames > 0) {
    say("[OK]   Frame CAN masuk normal.");
  }
  secFrames = 0;
  rawPrinted = 0;
}

void setup() {
  pinMode(PIN_LED_RED, OUTPUT);
  pinMode(PIN_LED_GREEN, OUTPUT);
  pinMode(PIN_ONBOARD, OUTPUT);
  indicators(false, false);

  Serial.begin(115200);
#if HAVE_SERIALUSB
  SerialUSB.begin(115200);
#endif
#if USE_SERIAL2
  Serial2.begin(9600);
#endif
  delay(1500);  // beri waktu monitor terbuka. TIDAK menunggu/cek DTR.

  say("");
  say("=== J1939 HW CHECK v2 (logger side) ===");
  out.toBt = true;
  out.print("jalur output: Serial");
#if HAVE_SERIALUSB
  out.print(" + SerialUSB");
#endif
#if USE_SERIAL2
  out.print(" + Serial2(BT 9600)");
#endif
  out.println();
  canOk = initCan();
  lastRetry = millis();
}

void loop() {
  uint32_t now = millis();

  digitalWrite(PIN_ONBOARD, !(((now / 500) & 1) != 0));  // heartbeat: firmware hidup

  if (!canOk) {
    indicators(((now / 100) & 1) != 0, false);            // merah kedip cepat
    if ((now - lastRetry) >= 3000) {
      lastRetry = now;
      say("[INIT] ulangi init CAN ...");
      canOk = initCan();
    }
    if ((now - lastReport) >= 1000) {
      lastReport = now;
      report(now);
    }
    return;
  }

  for (byte i = 0; i < 32 && CAN0.checkReceive() == CAN_MSGAVAIL; i++) {
    unsigned long rxId = 0; unsigned char len = 0; unsigned char buf[8] = {0};
    if (CAN0.readMsgBuf(&rxId, &len, buf) != CAN_OK) break;
    if (len > 8) len = 8;
    handleFrame(rxId, len, buf);
  }

  bool flowing = totalFrames && (now - lastRxMs) < 1000;
  indicators(false, flowing ? true : (((now / 500) & 1) != 0));  // hijau solid = frame masuk, kedip = standby

  if ((now - lastReport) >= 1000) {
    lastReport = now;
    report(now);
  }
}
