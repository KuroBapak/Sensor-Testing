#include <SPI.h>
#include <mcp_can.h>
#include <EEPROM.h>
#include <IWatchdog.h>

// Catatan build STM32duino: supaya %f di snprintf tampil, pilih
//   Tools > C Runtime Library > "Newlib Nano + Float Printf"

// ================= Hardware pins =================
#define CAN_CS_PIN   PA4
#define CAN_CRYSTAL  MCP_8MHZ
#define CAN_SPEED    CAN_250KBPS
MCP_CAN CAN0(CAN_CS_PIN);

#define PIN_BUZZER_EXT  PB7
#define PIN_LED_RED     PB6
#define PIN_LED_GREEN   PB5
// Output ACTIVE LOW: LOW = nyala, HIGH = mati

#define PGN_EEC1   61444UL

// ================= Fix v2 =================
// SA pengirim EEC1 yang dipercaya. 0xFFFF = terima dari SA mana pun (ANY).
// Isi dengan SA yang terbukti di log logger / "log_to_csv.py --check" (biasanya 0x00 = engine #1).
const uint16_t RPM_SA_FILTER = 0xFFFF;
// J1939-71: raw >= 0xFB00 = error / not-available (nilai valid max 0xFAFF = 8031.875 rpm)
const uint16_t RPM_RAW_INVALID_MIN = 0xFB00;
// Print RPM ke terminal dibatasi (Bluetooth 9600 baud cuma muat ~960 byte/detik)
const unsigned long RPM_PRINT_INTERVAL_MS = 500;

// MCP2515 register (akses langsung untuk verifikasi mode)
#define REG_CANSTAT 0x0E
#define REG_CNF3    0x28
#define REG_CNF2    0x29
#define REG_CNF1    0x2A

// ================= Config tersimpan di flash =================
struct Config {
  uint32_t magic;
  uint16_t version;
  float    rpmSafeBoundary;    // batas Safe -> Caution
  float    rpmDangerBoundary;  // batas Caution -> Danger
  uint32_t checksum;
};

const uint32_t CONFIG_MAGIC   = 0xC0FFEE42;
const uint16_t CONFIG_VERSION = 1;
const int      CONFIG_ADDR    = 0;
const float    DEFAULT_SAFE   = 3000.0;
const float    DEFAULT_DANGER = 5000.0;

Config cfg;

// ================= State sistem =================
enum SystemState { SYS_INIT, SYS_WAITING_RPM, SYS_NORMAL };
SystemState sysState = SYS_INIT;

enum RpmZone { ZONE_SAFE, ZONE_CAUTION, ZONE_DANGER };
RpmZone currentZone = ZONE_SAFE;
float lastRpm = 0;

bool blinkState = false;
unsigned long lastBlinkToggle = 0;
const unsigned long BLINK_INTERVAL_MS = 1000; // kedip CAUTION/DANGER

bool waitBlinkState = false;
unsigned long lastWaitBlinkToggle = 0;
const unsigned long WAIT_BLINK_INTERVAL_MS = 500; // kedip standby

unsigned long lastRxTime = 0;
const unsigned long RX_TIMEOUT_MS = 2000;
bool signalLost = true;

// ---- counter / throttle log (fix) ----
unsigned long lastRpmPrint = 0;
unsigned long lastInvalidLog = 0;
uint32_t invalidRpmFrames = 0;

// ---- runtime toggle (TIDAK disimpan ke flash, reset ke default tiap boot) ----
bool buzzerEnabled    = true; // true = ikut rules RPM, false = mati total
bool showRpmOnTerminal = true; // true = print RPM (max 2 Hz), false = senyap (deteksi tetap jalan)

// buffer command Bluetooth (char array, bukan String, biar ga fragmentasi heap)
char cmdBuffer[64];
uint8_t cmdLen = 0;

// ================= Prototypes =================
void loadConfig();
void saveConfig();
uint32_t calcChecksum(const Config &c);
void pollBluetoothCommands();
void handleCommand(char *line);
bool streq(const char *a, const char *b);
void printHelp();
void printStatus();
void handleSetBoundary(bool isSafe, const char *argStr);
void handleSetBuzzer(const char *argStr);
void handleShowRpm(const char *argStr);
void applyOutputs(bool buzzerFromZone, bool ledRed, bool ledGreen);
void allOff();
void blinkError();
void logBoth(const char *s);
void logBothLossy(const char *s);
uint8_t mcpReadReg(uint8_t reg);
bool enterListenOnly();

// ================= Setup =================
void setup() {
  Serial.begin(115200);
  Serial2.begin(9600);

  pinMode(PIN_BUZZER_EXT, OUTPUT);
  pinMode(PIN_LED_RED, OUTPUT);
  pinMode(PIN_LED_GREEN, OUTPUT);
  allOff();

  loadConfig();

  IWatchdog.begin(4000000); // 4 detik, auto-reset kalau loop() macet

  sysState = SYS_INIT;

  if (CAN0.begin(MCP_ANY, CAN_SPEED, CAN_CRYSTAL) == CAN_OK) {
    logBoth("[RX] MCP2515 init OK");
  } else {
    logBoth("[RX] MCP2515 init GAGAL - cek wiring/crystal/power HW-184");
    while (1) { blinkError(); }
  }

  // LISTEN-ONLY: node pasif (tidak ACK, tidak TX, tidak kirim error frame). Alat ini tidak pernah TX,
  // jadi NORMAL tidak ada gunanya dan hanya menambah risiko mengganggu bus truk.
  if (!enterListenOnly()) {
    logBoth("[RX] GAGAL masuk LISTEN-ONLY -> berhenti demi keamanan bus (watchdog akan reset)");
    while (1) { blinkError(); }
  }
  logBoth("[RX] Mode LISTEN-ONLY aktif, nunggu frame J1939 dari truk...");

  allOff();
  sysState = SYS_WAITING_RPM;
  lastWaitBlinkToggle = millis();

  printHelp();
}

// ================= Loop =================
void loop() {
  IWatchdog.reload();

  pollBluetoothCommands();

  // ---- baca CAN ----
  if (CAN0.checkReceive() == CAN_MSGAVAIL) {
    unsigned long rxId;
    unsigned char len = 0;
    unsigned char buf[8];
    CAN0.readMsgBuf(&rxId, &len, buf);

    if (rxId & 0x80000000) {
      unsigned long id29 = rxId & 0x1FFFFFFF;
      uint8_t pf = (id29 >> 16) & 0xFF;
      uint8_t ps = (id29 >> 8) & 0xFF;
      uint8_t sa = id29 & 0xFF;
      unsigned long pgn = (pf < 240) ? ((unsigned long)pf << 8)
                                      : (((unsigned long)pf << 8) | ps);

      if (pgn == PGN_EEC1 && len >= 5 &&
          (RPM_SA_FILTER == 0xFFFF || sa == RPM_SA_FILTER)) {
        uint16_t raw = buf[3] | ((uint16_t)buf[4] << 8);

        if (raw < RPM_RAW_INVALID_MIN) {
          // ---- RPM valid ----
          lastRpm = raw * 0.125f;
          lastRxTime = millis();
          signalLost = false;

          if (sysState == SYS_WAITING_RPM) {
            sysState = SYS_NORMAL;
            logBoth("[RX] RPM pertama diterima -> sistem mulai normal");
          }

          // deteksi TETAP jalan walau showRpmOnTerminal=false; throttle cuma untuk print
          if (showRpmOnTerminal && (millis() - lastRpmPrint) >= RPM_PRINT_INTERVAL_MS) {
            lastRpmPrint = millis();
            char buf2[48];
            snprintf(buf2, sizeof(buf2), "[RX] RPM=%.1f SA=0x%02X", lastRpm, sa);
            logBothLossy(buf2);
          }
        } else {
          // ---- error / not-available: JANGAN dipakai sebagai RPM, lastRxTime tidak di-refresh ----
          invalidRpmFrames++;
          if (showRpmOnTerminal && (millis() - lastInvalidLog) >= 2000) {
            lastInvalidLog = millis();
            char buf4[64];
            snprintf(buf4, sizeof(buf4), "[RX] EEC1 RPM tidak valid raw=0x%04X (total %lu)",
                     (unsigned)raw, (unsigned long)invalidRpmFrames);
            logBothLossy(buf4);
          }
        }
      }
    }
  }

  // ---- signal timeout ----
  if (!signalLost && millis() - lastRxTime > RX_TIMEOUT_MS) {
    signalLost = true;
    sysState = SYS_WAITING_RPM;
    waitBlinkState = false;
    lastWaitBlinkToggle = millis();
    logBoth("[RX] Sinyal J1939 hilang -> kembali ke mode standby");
  }

  // ---- mode standby ----
  if (sysState == SYS_WAITING_RPM) {
    if (millis() - lastWaitBlinkToggle >= WAIT_BLINK_INTERVAL_MS) {
      lastWaitBlinkToggle = millis();
      waitBlinkState = !waitBlinkState;
    }
    applyOutputs(false, false, waitBlinkState);
    return;
  }

  // ---- SYS_NORMAL: tentuin zona RPM (deteksi selalu jalan, ga kepengaruh ShowRpm) ----
  RpmZone newZone;
  if (lastRpm > cfg.rpmDangerBoundary)        newZone = ZONE_DANGER;
  else if (lastRpm >= cfg.rpmSafeBoundary)    newZone = ZONE_CAUTION;
  else                                        newZone = ZONE_SAFE;

  if (newZone != currentZone) {
    currentZone = newZone;
    blinkState = false;
    lastBlinkToggle = millis();
    const char *zoneName = currentZone == ZONE_SAFE ? "SAFE" :
                            currentZone == ZONE_CAUTION ? "CAUTION" : "DANGER";
    char buf3[48];
    snprintf(buf3, sizeof(buf3), "[RX] Zona berubah -> %s", zoneName);
    logBoth(buf3);
  }

  switch (currentZone) {
    case ZONE_SAFE:
      applyOutputs(false, false, true);
      break;

    case ZONE_CAUTION:
      if (millis() - lastBlinkToggle >= BLINK_INTERVAL_MS) {
        lastBlinkToggle = millis();
        blinkState = !blinkState;
      }
      applyOutputs(blinkState, blinkState, false);
      break;

    case ZONE_DANGER:
      applyOutputs(true, true, false);
      break;
  }
}

// ================= MCP2515: LISTEN-ONLY + verifikasi =================
uint8_t mcpReadReg(uint8_t reg) {
  digitalWrite(CAN_CS_PIN, LOW);
  SPI.transfer(0x03);
  SPI.transfer(reg);
  uint8_t v = SPI.transfer(0x00);
  digitalWrite(CAN_CS_PIN, HIGH);
  return v;
}

// true = chip terbukti (atau tidak bisa dicek, lihat peringatan) di LISTEN-ONLY.
bool enterListenOnly() {
  if (CAN0.setMode(MCP_LISTENONLY) != CAN_OK) return false;

  uint8_t cnf1 = mcpReadReg(REG_CNF1), cnf2 = mcpReadReg(REG_CNF2), cnf3 = mcpReadReg(REG_CNF3);
  if (cnf1 == cnf2 && cnf2 == cnf3 && (cnf1 == 0x00 || cnf1 == 0xFF)) {
    // jalur SPI mentah tidak cocok dengan library -> tidak bisa memverifikasi lewat register
    logBoth("[RX] PERINGATAN: baca register langsung tidak cocok, mode tidak bisa diverifikasi");
    return true;
  }
  uint8_t opmod = mcpReadReg(REG_CANSTAT) >> 5;     // 3 = LISTEN-ONLY
  if (opmod != 3) {
    char b[48];
    snprintf(b, sizeof(b), "[RX] opmod=%u (harus 3 = LISTEN-ONLY)", (unsigned)opmod);
    logBoth(b);
    return false;
  }
  return true;
}

// ================= Output =================
void applyOutputs(bool buzzerFromZone, bool ledRed, bool ledGreen) {
  bool buzzerFinal = buzzerEnabled ? buzzerFromZone : false;

  digitalWrite(PIN_BUZZER_EXT, !buzzerFinal);
  digitalWrite(PIN_LED_RED, !ledRed);
  digitalWrite(PIN_LED_GREEN, !ledGreen);
}

void allOff() {
  applyOutputs(false, false, false);
}

void blinkError() {
  digitalWrite(PIN_LED_RED, !digitalRead(PIN_LED_RED));
  delay(300);
}

// ================= EEPROM (flash emulation) =================
uint32_t calcChecksum(const Config &c) {
  uint32_t sbBits, dbBits;
  memcpy(&sbBits, &c.rpmSafeBoundary, 4);
  memcpy(&dbBits, &c.rpmDangerBoundary, 4);
  return c.magic ^ c.version ^ sbBits ^ dbBits;
}

void saveConfig() {
  cfg.checksum = calcChecksum(cfg);
  EEPROM.put(CONFIG_ADDR, cfg);
  logBoth("[CFG] Disimpan ke flash");
}

void loadConfig() {
  EEPROM.get(CONFIG_ADDR, cfg);
  bool valid = (cfg.magic == CONFIG_MAGIC) &&
               (cfg.version == CONFIG_VERSION) &&
               (cfg.checksum == calcChecksum(cfg)) &&
               (cfg.rpmSafeBoundary > 0) &&
               (cfg.rpmDangerBoundary > cfg.rpmSafeBoundary);

  if (!valid) {
    cfg.magic = CONFIG_MAGIC;
    cfg.version = CONFIG_VERSION;
    cfg.rpmSafeBoundary = DEFAULT_SAFE;
    cfg.rpmDangerBoundary = DEFAULT_DANGER;
    saveConfig();
    logBoth("[CFG] Flash kosong/rusak -> pakai default & simpan");
  } else {
    logBoth("[CFG] Config dimuat dari flash");
  }
}

// ================= Command Bluetooth =================
bool streq(const char *a, const char *b) {
  while (*a && *b) {
    if (tolower((unsigned char)*a) != tolower((unsigned char)*b)) return false;
    a++; b++;
  }
  return *a == *b;
}

void pollBluetoothCommands() {
  while (Serial2.available()) {
    char c = Serial2.read();
    if (c == '\r') continue;
    if (c == '\n') {
      cmdBuffer[cmdLen] = '\0';
      if (cmdLen > 0) handleCommand(cmdBuffer);
      cmdLen = 0;
    } else if (cmdLen < sizeof(cmdBuffer) - 1) {
      cmdBuffer[cmdLen++] = c;
    } else {
      cmdLen = 0; // overflow guard
    }
  }
}

void handleCommand(char *line) {
  char *cmd = strtok(line, " ");
  char *argStr = strtok(NULL, " ");
  if (cmd == NULL) return;

  if (streq(cmd, "/help")) {
    printHelp();
  } else if (streq(cmd, "/SetSafe") || streq(cmd, "/SetCaution")) {
    handleSetBoundary(true, argStr);
  } else if (streq(cmd, "/SetDanger")) {
    handleSetBoundary(false, argStr);
  } else if (streq(cmd, "/SetBuzzerExt")) {
    handleSetBuzzer(argStr);
  } else if (streq(cmd, "/ShowRpm")) {
    handleShowRpm(argStr);
  } else if (streq(cmd, "/status")) {
    printStatus();
  } else {
    char buf[64];
    snprintf(buf, sizeof(buf), "[CMD] Ga dikenal: %s (ketik /help)", cmd);
    logBoth(buf);
  }
}

void handleSetBoundary(bool isSafe, const char *argStr) {
  if (argStr == NULL) {
    logBoth("[CMD] Format salah, contoh: /SetSafe 3000");
    return;
  }
  float val = atof(argStr);
  if (val <= 0) {
    logBoth("[CMD] Nilai RPM harus > 0");
    return;
  }

  float newSafe   = isSafe ? val : cfg.rpmSafeBoundary;
  float newDanger = isSafe ? cfg.rpmDangerBoundary : val;

  if (newDanger <= newSafe) {
    logBoth("[CMD] Ditolak: batas Danger harus lebih besar dari Safe");
    return;
  }

  cfg.rpmSafeBoundary = newSafe;
  cfg.rpmDangerBoundary = newDanger;
  saveConfig();

  char buf[72];
  snprintf(buf, sizeof(buf), "[CMD] OK: Safe/Caution=%.0f, Caution/Danger=%.0f",
           cfg.rpmSafeBoundary, cfg.rpmDangerBoundary);
  logBoth(buf);
}

void handleSetBuzzer(const char *argStr) {
  if (argStr == NULL || (strcmp(argStr, "0") != 0 && strcmp(argStr, "1") != 0)) {
    logBoth("[CMD] Format salah, contoh: /SetBuzzerExt 1  (0=mati total, 1=ikut rules RPM)");
    return;
  }
  buzzerEnabled = (argStr[0] == '1');
  char buf[64];
  snprintf(buf, sizeof(buf), "[CMD] Buzzer %s", buzzerEnabled ? "AKTIF (ikut rules RPM)" : "MATI TOTAL");
  logBoth(buf);
}

void handleShowRpm(const char *argStr) {
  if (argStr == NULL || (strcmp(argStr, "0") != 0 && strcmp(argStr, "1") != 0)) {
    logBoth("[CMD] Format salah, contoh: /ShowRpm 1  (0=senyap, 1=tampilin RPM max 2x/detik)");
    return;
  }
  showRpmOnTerminal = (argStr[0] == '1');
  char buf[56];
  snprintf(buf, sizeof(buf), "[CMD] Tampilan RPM di terminal: %s", showRpmOnTerminal ? "ON" : "OFF");
  logBoth(buf);
}

void printHelp() {
  logBoth("=== Daftar Command ===");
  logBoth("/help               - tampilkan daftar command ini");
  logBoth("/SetSafe <rpm>      - set batas Safe->Caution (alias /SetCaution)");
  logBoth("/SetCaution <rpm>   - sama seperti /SetSafe");
  logBoth("/SetDanger <rpm>    - set batas Caution->Danger");
  logBoth("/SetBuzzerExt <0|1> - 0=mati total, 1=ikut rules RPM");
  logBoth("/ShowRpm <0|1>      - 0=senyap, 1=tampilin RPM max 2x/detik (deteksi tetap jalan)");
  logBoth("/status             - lihat config sekarang (bukan RPM live)");
}

void printStatus() {
  char saTxt[8];
  if (RPM_SA_FILTER == 0xFFFF) strcpy(saTxt, "ANY");
  else snprintf(saTxt, sizeof(saTxt), "0x%02X", (unsigned)RPM_SA_FILTER);

  char buf[128];
  snprintf(buf, sizeof(buf),
    "[STATUS] Safe=%.0f Danger=%.0f BuzzerExt=%s ShowRpm=%s SA=%s EEC1invalid=%lu",
    cfg.rpmSafeBoundary, cfg.rpmDangerBoundary,
    buzzerEnabled ? "ON" : "OFF",
    showRpmOnTerminal ? "ON" : "OFF",
    saTxt, (unsigned long)invalidRpmFrames);
  logBoth(buf);
}

// ================= Logging =================
void logBoth(const char *s) {
  Serial.println(s);
  Serial2.println(s);
}

// Untuk log yang sering (RPM): kalau buffer TX Bluetooth belum cukup, lewati Serial2 daripada memblokir loop().
void logBothLossy(const char *s) {
  int need = (int)strlen(s) + 2;                       // + CR LF
  if (Serial2.availableForWrite() >= need) Serial2.println(s);
  Serial.println(s);
}
