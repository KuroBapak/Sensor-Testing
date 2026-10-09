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

// ================= Mode bus (RUNTIME, bukan compile-time) =================
// DEFAULT SETIAP BOOT/RESET = 0 -> LISTEN-ONLY (tidak pernah ACK / TX / error frame), AMAN UNTUK TRUK.
// TIDAK disimpan ke flash. Mode meja test (NORMAL, memberi ACK ke node TX simulasi) hanya bisa dinyalakan
// lewat serial/Bluetooth:   /bench 1      kembali:   /bench 0      cek:   /bench
// !!! JANGAN /bench 1 di truk: chip jadi node aktif di bus truk !!!
#define MODE_TXT_LISTEN "LISTEN-ONLY"
#define MODE_TXT_BENCH  "NORMAL-ACK(BENCH)"
bool benchMode = false;

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
const uint16_t CONFIG_VERSION = 2;   // dinaikkan supaya default baru (1000/1500) menimpa config lama di flash
const int      CONFIG_ADDR    = 0;
const float    DEFAULT_SAFE   = 1000.0;
const float    DEFAULT_DANGER = 1500.0;

Config cfg;

// ================= State sistem =================
enum SystemState { SYS_INIT, SYS_WAITING_RPM, SYS_NORMAL };
SystemState sysState = SYS_INIT;

enum RpmZone { ZONE_SAFE, ZONE_CAUTION, ZONE_DANGER };
RpmZone currentZone = ZONE_SAFE;
float lastRpm = 0;

// ---- Pola beep per zona (non-blocking, berbasis millis) ----
// SAFE    : buzzer diam
// CAUTION : beep PANJANG (satu kali), diulang tiap CAUTION_PERIOD_MS
// DANGER  : beep-beep (dua kali pendek), diulang tiap DANGER_PERIOD_MS
unsigned long patternStart = 0;                    // di-reset tiap zona berubah
const unsigned long CAUTION_PERIOD_MS = 1000;      // ulang tiap 1 detik
const unsigned long CAUTION_BEEP_MS   = 700;       // beep panjang 700 ms, jeda 300 ms
const unsigned long DANGER_PERIOD_MS  = 1000;      // ulang tiap 1 detik
const unsigned long DANGER_BEEP_MS    = 150;       // tiap beep 150 ms
const unsigned long DANGER_GAP_MS     = 100;       // jeda antar beep 100 ms

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
bool applyBusMode(bool bench);
void handleBench(const char *argStr);

// ================= Setup =================
void setup() {
  Serial.begin(9600);
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

  // SETIAP boot: LISTEN-ONLY = node pasif (tidak ACK, tidak TX, tidak kirim error frame). Alat ini tidak pernah TX,
  // jadi NORMAL tidak ada gunanya di truk. Mode meja test hanya lewat perintah "/bench 1".
  benchMode = false;
  if (!applyBusMode(false)) {
    logBoth("[RX] GAGAL masuk LISTEN-ONLY -> berhenti demi keamanan bus (watchdog akan reset)");
    while (1) { blinkError(); }
  }
  logBoth("[RX] Mode LISTEN-ONLY aktif (bench=0), nunggu frame J1939...");

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
    patternStart = millis();   // pola beep mulai dari awal tiap ganti zona
    const char *zoneName = currentZone == ZONE_SAFE ? "SAFE" :
                            currentZone == ZONE_CAUTION ? "CAUTION" : "DANGER";
    char buf3[48];
    snprintf(buf3, sizeof(buf3), "[RX] Zona berubah -> %s", zoneName);
    logBoth(buf3);
  }

  switch (currentZone) {
    case ZONE_SAFE:
      // buzzer diam, LED hijau nyala
      applyOutputs(false, false, true);
      break;

    case ZONE_CAUTION: {
      // beep panjang: ON 700 ms, OFF 300 ms
      unsigned long phase = (millis() - patternStart) % CAUTION_PERIOD_MS;
      bool beepOn = (phase < CAUTION_BEEP_MS);
      applyOutputs(beepOn, beepOn, false);   // LED merah ikut kedip bareng beep
      break;
    }

    case ZONE_DANGER: {
      // beep-beep: ON 150 / OFF 100 / ON 150 / OFF sisa 1 detik
      unsigned long phase = (millis() - patternStart) % DANGER_PERIOD_MS;
      bool beepOn = (phase < DANGER_BEEP_MS) ||
                    (phase >= (DANGER_BEEP_MS + DANGER_GAP_MS) &&
                     phase <  (2 * DANGER_BEEP_MS + DANGER_GAP_MS));
      applyOutputs(beepOn, true, false);     // LED merah nyala terus di DANGER
      break;
    }
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

// true = chip terbukti (atau tidak bisa dicek, lihat peringatan) berada di mode yang diminta.
// bench=false -> LISTEN-ONLY (opmod 3), bench=true -> NORMAL (opmod 0).
bool applyBusMode(bool bench) {
  const uint8_t wantMode  = bench ? MCP_NORMAL : MCP_LISTENONLY;
  const uint8_t wantOpmod = bench ? 0 : 3;
  if (CAN0.setMode(wantMode) != CAN_OK) return false;

  uint8_t cnf1 = mcpReadReg(REG_CNF1), cnf2 = mcpReadReg(REG_CNF2), cnf3 = mcpReadReg(REG_CNF3);
  if (cnf1 == cnf2 && cnf2 == cnf3 && (cnf1 == 0x00 || cnf1 == 0xFF)) {
    // jalur SPI mentah tidak cocok dengan library -> tidak bisa memverifikasi lewat register
    logBoth("[RX] PERINGATAN: baca register langsung tidak cocok, mode tidak bisa diverifikasi");
    return true;
  }
  uint8_t opmod = mcpReadReg(REG_CANSTAT) >> 5;     // 0 = NORMAL, 3 = LISTEN-ONLY
  if (opmod != wantOpmod) {
    char b[64];
    snprintf(b, sizeof(b), "[RX] opmod=%u (harus %u)", (unsigned)opmod, (unsigned)wantOpmod);
    logBoth(b);
    return false;
  }
  return true;
}

// /bench <0|1> : 0 = LISTEN-ONLY (aman untuk truk), 1 = NORMAL-ACK (HANYA meja test). Tanpa argumen = lihat status.
void handleBench(const char *argStr) {
  if (argStr == NULL) {
    logBoth(benchMode ? "[CMD] BENCH=1 AKTIF (NORMAL-ACK, aktif di bus). Kirim /bench 0 untuk kembali"
                      : "[CMD] BENCH=0 (LISTEN-ONLY, aman untuk truk). Default setiap boot");
    return;
  }
  if (strcmp(argStr, "0") != 0 && strcmp(argStr, "1") != 0) {
    logBoth("[CMD] Format salah, contoh: /bench 1  (0=LISTEN-ONLY aman untuk truk, 1=NORMAL-ACK hanya meja test)");
    return;
  }
  bool want = (argStr[0] == '1');
  if (want == benchMode) {
    logBoth(want ? "[CMD] BENCH sudah 1 (NORMAL-ACK)" : "[CMD] BENCH sudah 0 (LISTEN-ONLY)");
    return;
  }

  if (want) {
    if (applyBusMode(true)) {
      benchMode = true;
      logBoth("[CMD] BENCH=1 AKTIF: chip NORMAL (memberi ACK, aktif di bus). Hanya meja test, JANGAN di truk!");
    } else {
      logBoth("[CMD] BENCH=1 GAGAL: chip tidak masuk NORMAL -> kembali ke LISTEN-ONLY");
      if (!applyBusMode(false)) {
        logBoth("[CMD] GAGAL kembali ke LISTEN-ONLY -> reset demi keamanan bus");
        while (1) { blinkError(); }               // watchdog mereset -> boot di LISTEN-ONLY
      }
    }
  } else {
    if (applyBusMode(false)) {
      benchMode = false;
      logBoth("[CMD] BENCH=0: LISTEN-ONLY (pasif, aman untuk truk)");
    } else {
      logBoth("[CMD] BENCH=0 GAGAL masuk LISTEN-ONLY -> reset demi keamanan bus");
      while (1) { blinkError(); }                 // watchdog mereset -> boot di LISTEN-ONLY
    }
  }
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
  } else if (streq(cmd, "/bench") || streq(cmd, "bench")) {
    handleBench(argStr);
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
  logBoth("/bench <0|1>        - 0=LISTEN-ONLY (aman truk, default tiap boot), 1=NORMAL-ACK (HANYA meja test)");
  logBoth("/status             - lihat config sekarang (bukan RPM live)");
}

void printStatus() {
  char saTxt[8];
  if (RPM_SA_FILTER == 0xFFFF) strcpy(saTxt, "ANY");
  else snprintf(saTxt, sizeof(saTxt), "0x%02X", (unsigned)RPM_SA_FILTER);

  char buf[160];
  snprintf(buf, sizeof(buf),
    "[STATUS] Safe=%.0f Danger=%.0f BuzzerExt=%s ShowRpm=%s SA=%s EEC1invalid=%lu Mode=%s",
    cfg.rpmSafeBoundary, cfg.rpmDangerBoundary,
    buzzerEnabled ? "ON" : "OFF",
    showRpmOnTerminal ? "ON" : "OFF",
    saTxt, (unsigned long)invalidRpmFrames, benchMode ? MODE_TXT_BENCH : MODE_TXT_LISTEN);
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
