// =====================================================================
//  J1939 RPM Monitor - MCP2515 (HW-184) + STM32
//  - Default LISTEN-ONLY tiap boot (pasif: tidak ACK / TX / error frame). /bench 1 hanya untuk meja test.
//  - Serial: SEMENTARA log ke Serial1 DAN Serial2 (panggilan langsung, bukan lewat pointer) sampai jalur yang benar ketahuan.
//  - Non-blocking: log lewat ring buffer, loop() tidak pernah menunggu UART.
//  - Zona RPM pakai hysteresis + debounce keluar: masuk DANGER langsung bunyi, beep makin cepat saat RPM naik.
//  - Tidak butuh "Newlib Nano + Float Printf" (semua print pakai integer).
// =====================================================================
#include <SPI.h>
#include <mcp_can.h>
#include <EEPROM.h>
#include <IWatchdog.h>

// ================= Serial =================
// SEMENTARA: log dikirim ke Serial1 (PA9/PA10) DAN Serial2 (PA2/PA3); command diterima dari keduanya.
// Saat boot tiap port mencetak "[PORT] ini SerialX" -> label yang muncul di terminalmu = port yang benar.
// Setelah ketahuan, set NUM_PORTS ke 1 dan arahkan helper port* di bawah ke satu port itu saja.
#define LINK_BAUD  9600         // default HC-05/06. Terminal PC harus diset sama.

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

// RPM valid menurut J1939-71: raw < 0xFB00 (0xFB00..0xFFFF = error / not-available, valid max 8031.875 rpm)
const uint16_t RPM_RAW_INVALID_MIN = 0xFB00;

// EEC1 di kendaraan ini ~100 Hz (tiap 10 ms). Print RPM dibatasi supaya log tidak penuh.
const unsigned long RPM_PRINT_INTERVAL_MS = 200;

// ================= Anti on-off cepat (hysteresis) =================
// Naik zona: langsung begitu RPM melewati batas (1 frame cukup).
// Turun zona: RPM harus di bawah (batas - ZONE_HYST_RPM) TERUS-MENERUS selama ZONE_EXIT_DELAY_MS.
// Satu frame RPM yang "bergetar" di bawah batas tidak akan mematikan alarm.
const float         ZONE_HYST_RPM      = 50.0f;   // sebelumnya 100
const unsigned long ZONE_EXIT_DELAY_MS = 250;     // sebelumnya 500

// ================= Mode bus (RUNTIME, tidak disimpan) =================
// Default setiap boot = LISTEN-ONLY. /bench 1 = NORMAL (kirim ACK) HANYA untuk meja test. JANGAN di kendaraan.
bool benchMode = false;

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
const float    DEFAULT_SAFE   = 2000.0;
const float    DEFAULT_DANGER = 2500.0;

Config cfg;

// ================= State sistem =================
enum SystemState { SYS_INIT, SYS_WAITING_RPM, SYS_NORMAL };
SystemState sysState = SYS_INIT;

enum RpmZone { ZONE_SAFE, ZONE_CAUTION, ZONE_DANGER };
RpmZone currentZone = ZONE_SAFE;
float lastRpm = 0;
bool zoneExiting = false;            // sedang di bawah ambang keluar zona
unsigned long zoneExitSince = 0;     // sejak kapan

bool blinkState = false;
unsigned long lastBlinkToggle = 0;
// Kedip DANGER: interval dihitung dari RPM (lihat dangerBlinkInterval). CAUTION solid.

bool waitBlinkState = false;
unsigned long lastWaitBlinkToggle = 0;
const unsigned long WAIT_BLINK_INTERVAL_MS = 500; // kedip standby

unsigned long lastRxTime = 0;
const unsigned long RX_TIMEOUT_MS = 2000;
bool signalLost = true;

unsigned long lastRpmPrint = 0;
uint32_t invalidRpmFrames = 0;
unsigned long lastStandbyMsg = 0;

// ---- runtime toggle (TIDAK disimpan ke flash, reset ke default tiap boot) ----
bool buzzerEnabled     = true; // true = ikut rules RPM, false = mati total
bool showRpmOnTerminal = true; // true = print RPM (max 5 Hz), false = senyap (deteksi tetap jalan)

// ---- port serial: tiap port punya ring buffer TX sendiri + buffer command sendiri ----
// Satu port yang macet/tidak tersambung tidak mengganggu port lain maupun loop().
#define NUM_PORTS    2
#define LOG_BUF_SIZE 1024
struct PortState {
  char     tx[LOG_BUF_SIZE];
  uint16_t head;       // posisi tulis
  uint16_t tail;       // posisi kirim
  char     cmd[64];
  uint8_t  cmdLen;
};
PortState ports[NUM_PORTS];

// Helper port: panggilan LANGSUNG ke objek HardwareSerial (tanpa pointer/virtual).
static int  portAvail(uint8_t i)  { return i == 0 ? Serial1.available()        : Serial2.available(); }
static int  portRead(uint8_t i)   { return i == 0 ? Serial1.read()             : Serial2.read(); }
static int  portTxFree(uint8_t i) { return i == 0 ? Serial1.availableForWrite() : Serial2.availableForWrite(); }
static void portWrite(uint8_t i, uint8_t c) { if (i == 0) Serial1.write(c); else Serial2.write(c); }
uint32_t logDropped = 0;    // baris yang dibuang karena buffer penuh

// ================= Prototypes =================
void loadConfig();
void saveConfig();
uint32_t calcChecksum(const Config &c);
void pollCommands();
void handleCommand(char *line);
bool streq(const char *a, const char *b);
void printHelp();
void printStatus();
void handleSetBoundary(bool isSafe, const char *argStr);
void handleSetBuzzer(const char *argStr);
void handleShowRpm(const char *argStr);
void handleBench(const char *argStr);
bool applyBusMode(bool bench);
RpmZone evalZone(RpmZone cur, float rpm);
void applyOutputs(bool buzzerFromZone, bool ledRed, bool ledGreen);
void allOff();
void blinkError();
void logLine(const char *s);
static void pushLine(uint8_t i, const char *s);
void pumpLog();
void pollPort(uint8_t i);
void flushLog(unsigned long maxMs);

static unsigned long toUL(float v) { return (unsigned long)(v + 0.5f); }

// Interval kedip DANGER: 300 ms tepat di batas Danger -> 80 ms di Danger + 500 RPM (makin tinggi makin cepat).
// Kalau buzzer terdengar terlalu "nyambung", ganti 220.0f jadi 180.0f (minimum jadi ~120 ms).
static unsigned long dangerBlinkInterval(float rpm) {
  float over = rpm - cfg.rpmDangerBoundary;
  if (over < 0) over = 0;
  if (over > 500) over = 500;
  return 300UL - (unsigned long)(over * 220.0f / 500.0f);
}

// ================= Setup =================
void setup() {
  // Set HIGH (= mati, active-low) SEBELUM jadi output, supaya buzzer tidak "klik" saat boot
  digitalWrite(PIN_BUZZER_EXT, HIGH);
  digitalWrite(PIN_LED_RED, HIGH);
  digitalWrite(PIN_LED_GREEN, HIGH);
  pinMode(PIN_BUZZER_EXT, OUTPUT);
  pinMode(PIN_LED_RED, OUTPUT);
  pinMode(PIN_LED_GREEN, OUTPUT);
  allOff();

  Serial1.setRx(PA10);
  Serial1.setTx(PA9);
  Serial1.begin(LINK_BAUD);
  Serial2.begin(LINK_BAUD);

  loadConfig();

  IWatchdog.begin(4000000); // 4 detik, auto-reset kalau loop() macet

  // Penanda port + versi: kalau ini muncul di terminal, jalur TX sudah benar
  pushLine(0, "[PORT] ini Serial1 (PA9/PA10)");
  pushLine(1, "[PORT] ini Serial2 (PA2/PA3)");
  flushLog(400);

  sysState = SYS_INIT;

  if (CAN0.begin(MCP_ANY, CAN_SPEED, CAN_CRYSTAL) == CAN_OK) {
    logLine("[RX] MCP2515 init OK");
  } else {
    logLine("[RX] MCP2515 init GAGAL - cek wiring/crystal/power HW-184");
    while (1) { blinkError(); }   // watchdog reset -> coba init lagi
  }
  flushLog(300);

  // Setiap boot: LISTEN-ONLY. Kalau gagal, jangan lanjut (watchdog reset) daripada jadi node aktif di bus.
  benchMode = false;
  if (!applyBusMode(false)) {
    logLine("[RX] GAGAL masuk LISTEN-ONLY -> berhenti demi keamanan bus");
    while (1) { blinkError(); }
  }
  logLine("[RX] Mode LISTEN-ONLY aktif (bench=0), nunggu frame J1939...");

  allOff();
  sysState = SYS_WAITING_RPM;
  lastWaitBlinkToggle = millis();

  printHelp();
  flushLog(1500);
}

// ================= Loop =================
void loop() {
  IWatchdog.reload();

  pumpLog();
  pollCommands();

  // ---- baca CAN (kuras buffer MCP2515, maks 8 frame per putaran supaya loop tetap responsif) ----
  for (uint8_t n = 0; n < 8 && CAN0.checkReceive() == CAN_MSGAVAIL; n++) {
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

      if (pgn == PGN_EEC1 && len >= 5) {
        uint16_t raw = buf[3] | ((uint16_t)buf[4] << 8);

        if (raw < RPM_RAW_INVALID_MIN) {
          lastRpm = raw * 0.125f;
          lastRxTime = millis();
          signalLost = false;

          if (sysState == SYS_WAITING_RPM) {
            sysState = SYS_NORMAL;
            logLine("[RX] RPM pertama diterima -> sistem mulai normal");
          }

          // deteksi TETAP jalan walau showRpmOnTerminal=false; ini cuma soal print
          if (showRpmOnTerminal && (millis() - lastRpmPrint) >= RPM_PRINT_INTERVAL_MS) {
            lastRpmPrint = millis();
            char b[40];
            snprintf(b, sizeof(b), "[RX] RPM=%lu SA=0x%02X", toUL(lastRpm), (unsigned)sa);
            logLine(b);
          }
        } else {
          // error / not-available: JANGAN dipakai sebagai RPM, lastRxTime tidak di-refresh
          invalidRpmFrames++;
        }
      }
    }
  }

  // ---- signal timeout ----
  if (!signalLost && millis() - lastRxTime > RX_TIMEOUT_MS) {
    signalLost = true;
    sysState = SYS_WAITING_RPM;
    currentZone = ZONE_SAFE;
    zoneExiting = false;
    waitBlinkState = false;
    lastWaitBlinkToggle = millis();
    logLine("[RX] Sinyal J1939 hilang -> kembali ke mode standby");
  }

  // ---- mode standby ----
  if (sysState == SYS_WAITING_RPM) {
    if (millis() - lastWaitBlinkToggle >= WAIT_BLINK_INTERVAL_MS) {
      lastWaitBlinkToggle = millis();
      waitBlinkState = !waitBlinkState;
    }
    applyOutputs(false, false, waitBlinkState);
    if (millis() - lastStandbyMsg >= 10000) {
      lastStandbyMsg = millis();
      logLine("[RX] standby: belum ada EEC1 valid (RPM)");
    }
    return;
  }

  // ---- SYS_NORMAL: tentukan zona (pakai hysteresis) ----
  RpmZone newZone = evalZone(currentZone, lastRpm);
  if (newZone != currentZone) {
    currentZone = newZone;
    blinkState = (currentZone == ZONE_DANGER);   // masuk DANGER: beep pertama langsung bunyi
    lastBlinkToggle = millis();
    const char *zoneName = currentZone == ZONE_SAFE ? "SAFE" :
                            currentZone == ZONE_CAUTION ? "CAUTION" : "DANGER";
    char b[40];
    snprintf(b, sizeof(b), "[RX] Zona berubah -> %s", zoneName);
    logLine(b);
  }

  switch (currentZone) {
    case ZONE_SAFE:
      applyOutputs(false, false, true);
      break;

    case ZONE_CAUTION:
      applyOutputs(true, true, false);   // SOLID, tidak pernah berkedip
      break;

    case ZONE_DANGER:
      if (millis() - lastBlinkToggle >= dangerBlinkInterval(lastRpm)) {
        lastBlinkToggle = millis();
        blinkState = !blinkState;
      }
      applyOutputs(blinkState, blinkState, false);
      break;
  }
}

// ================= Logika zona =================
RpmZone evalZone(RpmZone cur, float rpm) {
  // ---- naik zona: langsung ----
  if (rpm > cfg.rpmDangerBoundary) {
    if (cur != ZONE_DANGER) zoneExiting = false;
    return ZONE_DANGER;
  }
  if (cur == ZONE_SAFE) {
    return (rpm >= cfg.rpmSafeBoundary) ? ZONE_CAUTION : ZONE_SAFE;
  }

  // ---- turun zona: harus stabil di bawah ambang keluar selama ZONE_EXIT_DELAY_MS ----
  float exitLevel = (cur == ZONE_DANGER) ? cfg.rpmDangerBoundary - ZONE_HYST_RPM
                                         : cfg.rpmSafeBoundary   - ZONE_HYST_RPM;
  if (rpm >= exitLevel) {          // masih di zona ini (atau di zona hysteresis)
    zoneExiting = false;
    return cur;
  }
  if (!zoneExiting) {
    zoneExiting = true;
    zoneExitSince = millis();
  }
  if (millis() - zoneExitSince < ZONE_EXIT_DELAY_MS) return cur;

  zoneExiting = false;
  if (cur == ZONE_DANGER)
    return (rpm >= cfg.rpmSafeBoundary - ZONE_HYST_RPM) ? ZONE_CAUTION : ZONE_SAFE;
  return ZONE_SAFE;
}

// ================= MCP2515: mode bus =================
// setMode() di library mcp_can membaca balik register CANCTRL, jadi CAN_OK = chip benar-benar di mode itu.
// bench=false -> LISTEN-ONLY (pasif), bench=true -> NORMAL (kirim ACK).
bool applyBusMode(bool bench) {
  return CAN0.setMode(bench ? MCP_NORMAL : MCP_LISTENONLY) == CAN_OK;
}

// /bench <0|1> : 0 = LISTEN-ONLY (aman kendaraan), 1 = NORMAL-ACK (HANYA meja test). Tanpa argumen = lihat status.
void handleBench(const char *argStr) {
  if (argStr == NULL) {
    logLine(benchMode ? "[CMD] BENCH=1 AKTIF (NORMAL-ACK, aktif di bus). Kirim /bench 0 untuk kembali"
                      : "[CMD] BENCH=0 (LISTEN-ONLY, aman untuk kendaraan). Default setiap boot");
    return;
  }
  if (strcmp(argStr, "0") != 0 && strcmp(argStr, "1") != 0) {
    logLine("[CMD] Format salah, contoh: /bench 1  (0=LISTEN-ONLY aman, 1=NORMAL-ACK hanya meja test)");
    return;
  }
  bool want = (argStr[0] == '1');
  if (want == benchMode) {
    logLine(want ? "[CMD] BENCH sudah 1 (NORMAL-ACK)" : "[CMD] BENCH sudah 0 (LISTEN-ONLY)");
    return;
  }

  if (want) {
    if (applyBusMode(true)) {
      benchMode = true;
      logLine("[CMD] BENCH=1 AKTIF: chip NORMAL (kirim ACK). Hanya meja test, JANGAN di kendaraan!");
    } else {
      logLine("[CMD] BENCH=1 GAGAL -> kembali ke LISTEN-ONLY");
      if (!applyBusMode(false)) {
        logLine("[CMD] GAGAL kembali ke LISTEN-ONLY -> reset demi keamanan bus");
        while (1) { blinkError(); }   // watchdog reset -> boot di LISTEN-ONLY
      }
    }
  } else {
    if (applyBusMode(false)) {
      benchMode = false;
      logLine("[CMD] BENCH=0: LISTEN-ONLY (pasif, aman untuk kendaraan)");
    } else {
      logLine("[CMD] BENCH=0 GAGAL masuk LISTEN-ONLY -> reset demi keamanan bus");
      while (1) { blinkError(); }     // watchdog reset -> boot di LISTEN-ONLY
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

// Hanya dipakai di jalur error fatal (while(1)); watchdog sengaja dibiarkan reset.
void blinkError() {
  digitalWrite(PIN_LED_RED, !digitalRead(PIN_LED_RED));
  pumpLog();     // supaya pesan error sempat terkirim
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
  logLine("[CFG] Disimpan ke flash");
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
    logLine("[CFG] Flash kosong/rusak -> pakai default & simpan");
  } else {
    logLine("[CFG] Config dimuat dari flash");
  }
}

// ================= Command =================
bool streq(const char *a, const char *b) {
  while (*a && *b) {
    if (tolower((unsigned char)*a) != tolower((unsigned char)*b)) return false;
    a++; b++;
  }
  return *a == *b;
}

void pollCommands() {
  for (uint8_t i = 0; i < NUM_PORTS; i++) pollPort(i);
}

void pollPort(uint8_t i) {
  PortState &p = ports[i];
  while (portAvail(i)) {
    char c = portRead(i);
    if (c == '\r') continue;
    if (c == '\n') {
      p.cmd[p.cmdLen] = '\0';
      if (p.cmdLen > 0) handleCommand(p.cmd);
      p.cmdLen = 0;
    } else if (p.cmdLen < sizeof(p.cmd) - 1) {
      p.cmd[p.cmdLen++] = c;
    } else {
      p.cmdLen = 0; // overflow guard
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
    char b[64];
    snprintf(b, sizeof(b), "[CMD] Ga dikenal: %.30s (ketik /help)", cmd);
    logLine(b);
  }
}

void handleSetBoundary(bool isSafe, const char *argStr) {
  if (argStr == NULL) {
    logLine("[CMD] Format salah, contoh: /SetSafe 3000");
    return;
  }
  float val = atof(argStr);
  if (val <= 0 || val > 8000) {
    logLine("[CMD] Nilai RPM harus 1 - 8000");
    return;
  }

  float newSafe   = isSafe ? val : cfg.rpmSafeBoundary;
  float newDanger = isSafe ? cfg.rpmDangerBoundary : val;

  if (newDanger <= newSafe) {
    logLine("[CMD] Ditolak: batas Danger harus lebih besar dari Safe");
    return;
  }

  cfg.rpmSafeBoundary = newSafe;
  cfg.rpmDangerBoundary = newDanger;
  saveConfig();

  char b[72];
  snprintf(b, sizeof(b), "[CMD] OK: Safe/Caution=%lu, Caution/Danger=%lu",
           toUL(cfg.rpmSafeBoundary), toUL(cfg.rpmDangerBoundary));
  logLine(b);
}

void handleSetBuzzer(const char *argStr) {
  if (argStr == NULL || (strcmp(argStr, "0") != 0 && strcmp(argStr, "1") != 0)) {
    logLine("[CMD] Format salah, contoh: /SetBuzzerExt 1  (0=mati total, 1=ikut rules RPM)");
    return;
  }
  buzzerEnabled = (argStr[0] == '1');
  logLine(buzzerEnabled ? "[CMD] Buzzer AKTIF (ikut rules RPM)" : "[CMD] Buzzer MATI TOTAL");
}

void handleShowRpm(const char *argStr) {
  if (argStr == NULL || (strcmp(argStr, "0") != 0 && strcmp(argStr, "1") != 0)) {
    logLine("[CMD] Format salah, contoh: /ShowRpm 1  (0=senyap, 1=tampilin RPM max 5x/detik)");
    return;
  }
  showRpmOnTerminal = (argStr[0] == '1');
  logLine(showRpmOnTerminal ? "[CMD] Tampilan RPM di terminal: ON" : "[CMD] Tampilan RPM di terminal: OFF");
}

void printHelp() {
  logLine("=== Daftar Command ===");
  logLine("/help               - tampilkan daftar command ini");
  logLine("/SetSafe <rpm>      - set batas Safe->Caution (alias /SetCaution)");
  logLine("/SetCaution <rpm>   - sama seperti /SetSafe");
  logLine("/SetDanger <rpm>    - set batas Caution->Danger");
  logLine("/SetBuzzerExt <0|1> - 0=mati total, 1=ikut rules RPM");
  logLine("/ShowRpm <0|1>      - 0=senyap, 1=tampilin RPM max 5x/detik");
  logLine("/bench <0|1>        - 0=LISTEN-ONLY (default tiap boot), 1=NORMAL-ACK (HANYA meja test)");
  logLine("/status             - config + kondisi sekarang");
}

void printStatus() {
  char b[160];
  snprintf(b, sizeof(b),
    "[STATUS] Safe=%lu Danger=%lu BuzzerExt=%s ShowRpm=%s Mode=%s Sig=%s Rpm=%lu EEC1inv=%lu LogDrop=%lu",
    toUL(cfg.rpmSafeBoundary), toUL(cfg.rpmDangerBoundary),
    buzzerEnabled ? "ON" : "OFF",
    showRpmOnTerminal ? "ON" : "OFF",
    benchMode ? "NORMAL-ACK(BENCH)" : "LISTEN-ONLY",
    signalLost ? "LOST" : "OK",
    signalLost ? 0UL : toUL(lastRpm),
    (unsigned long)invalidRpmFrames, (unsigned long)logDropped);
  logLine(b);
}

// ================= Logging (non-blocking) =================
static void pushLine(uint8_t i, const char *s) {
  PortState &p = ports[i];
  size_t need = strlen(s) + 2;                                         // + CR LF
  size_t used = (p.head + LOG_BUF_SIZE - p.tail) % LOG_BUF_SIZE;
  if (need > (size_t)(LOG_BUF_SIZE - 1) - used) { logDropped++; return; }
  while (*s) { p.tx[p.head] = *s++; p.head = (p.head + 1) % LOG_BUF_SIZE; }
  p.tx[p.head] = '\r'; p.head = (p.head + 1) % LOG_BUF_SIZE;
  p.tx[p.head] = '\n'; p.head = (p.head + 1) % LOG_BUF_SIZE;
}

// Kirim 1 baris ke semua port. Kalau buffer sebuah port tidak cukup, baris dibuang (dihitung di LogDrop), TIDAK menunggu.
void logLine(const char *s) {
  for (uint8_t i = 0; i < NUM_PORTS; i++) pushLine(i, s);
}

// Dipanggil tiap loop(): kirim ke UART hanya sebanyak yang muat di buffer TX hardware, tanpa menunggu.
void pumpLog() {
  for (uint8_t i = 0; i < NUM_PORTS; i++) {
    PortState &p = ports[i];
    while (p.tail != p.head && portTxFree(i) > 0) {
      portWrite(i, (uint8_t)p.tx[p.tail]);
      p.tail = (p.tail + 1) % LOG_BUF_SIZE;
    }
  }
}

// Hanya untuk boot: kosongkan antrean log dengan menunggu (maks maxMs) supaya teks boot pasti keluar
// walau setup() macet/reset sesudahnya. Di loop() TIDAK dipakai (tetap non-blocking).
void flushLog(unsigned long maxMs) {
  unsigned long t0 = millis();
  while (millis() - t0 < maxMs) {
    bool pending = false;
    for (uint8_t i = 0; i < NUM_PORTS; i++) if (ports[i].tail != ports[i].head) pending = true;
    if (!pending) break;
    pumpLog();
    IWatchdog.reload();
  }
}