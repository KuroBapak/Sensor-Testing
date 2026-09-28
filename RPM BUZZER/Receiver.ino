#include <SPI.h>
#include <mcp_can.h>

// HW-184 (SPI1 default STM32F103)
#define CAN_CS_PIN   PA4
#define CAN_CRYSTAL  MCP_8MHZ
#define CAN_SPEED    CAN_250KBPS
MCP_CAN CAN0(CAN_CS_PIN);

// Output alarm (ACTIVE LOW: LOW = nyala, HIGH = mati)
#define PIN_BUZZER_EXT  PB7
#define PIN_LED_RED     PB6
#define PIN_LED_GREEN   PB5
#define PIN_BUZZER_INT  PB4  // dipasangin sama buzzer external (nyala/mati/kedip bareng)

#define PGN_EEC1   61444UL

// ---------------- Threshold RPM (logic baru) ----------------
const float RPM_CAUTION_LOW = 3000.0; // di bawah ini = aman
const float RPM_DANGER      = 5000.0; // di atas ini = bahaya, buzzer full
const unsigned long BLINK_INTERVAL_MS = 1000; // kedip tiap 1 detik (on 1s / off 1s) - dipakai pas zona CAUTION

// ---------------- State sistem ----------------
// SYS_INIT        : baru nyala, CAN belum siap -> hanya LED merah berkedip (lihat blinkError()), buzzer & hijau mati
// SYS_WAITING_RPM : CAN sudah init OK, nunggu frame RPM pertama -> LED merah mati, LED hijau berkedip, buzzer mati
// SYS_NORMAL      : sudah pernah dapat RPM valid -> jalan logic zona (SAFE/CAUTION/DANGER) seperti biasa
enum SystemState { SYS_INIT, SYS_WAITING_RPM, SYS_NORMAL };
SystemState sysState = SYS_INIT;

const unsigned long WAIT_BLINK_INTERVAL_MS = 500; // kedip hijau saat standby/nunggu data (dibedain dari kedip caution)
bool waitBlinkState = false;
unsigned long lastWaitBlinkToggle = 0;

enum RpmZone { ZONE_SAFE, ZONE_CAUTION, ZONE_DANGER };
RpmZone currentZone = ZONE_SAFE;
float lastRpm = 0;

bool blinkState = false;
unsigned long lastBlinkToggle = 0;

unsigned long lastRxTime = 0;
const unsigned long RX_TIMEOUT_MS = 2000;
bool signalLost = true;

void setup() {
  Serial.begin(115200);
  Serial2.begin(9600);

  pinMode(PIN_BUZZER_EXT, OUTPUT);
  pinMode(PIN_LED_RED, OUTPUT);
  pinMode(PIN_LED_GREEN, OUTPUT);
  pinMode(PIN_BUZZER_INT, OUTPUT);
  allOff();

  sysState = SYS_INIT; // pastikan eksplisit: belum ada buzzer/led merah nyala solid selama init

  if (CAN0.begin(MCP_ANY, CAN_SPEED, CAN_CRYSTAL) == CAN_OK) {
    logBoth("[RX] MCP2515 init OK");
  } else {
    logBoth("[RX] MCP2515 init GAGAL - cek wiring/crystal/power HW-184");
    while (1) { blinkError(); } // hanya LED merah berkedip, buzzer & hijau tetap mati (allOff() sudah dipanggil di atas)
  }
  CAN0.setMode(MCP_NORMAL);
  logBoth("[RX] Siap, nunggu frame J1939 dari truk...");

  // init selesai -> masuk mode standby: LED merah mati, LED hijau mulai berkedip sampai RPM pertama didapat
  allOff();
  sysState = SYS_WAITING_RPM;
  lastWaitBlinkToggle = millis();
}

void loop() {
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

      if (pgn == PGN_EEC1 && len >= 5) {
        uint16_t raw = buf[3] | ((uint16_t)buf[4] << 8);
        if (raw != 0xFFFF) {
          lastRpm = raw * 0.125f;
          lastRxTime = millis();
          signalLost = false;

          if (sysState == SYS_WAITING_RPM) {
            sysState = SYS_NORMAL;
            logBoth("[RX] RPM pertama diterima -> sistem mulai normal");
          }

          logBoth("[RX] RPM=" + String(lastRpm, 1) + " SA=0x" + String(sa, HEX));
        }
      }
    }
  }

  // ---- signal timeout ----
  if (!signalLost && millis() - lastRxTime > RX_TIMEOUT_MS) {
    signalLost = true;
    // sinyal hilang lagi -> balik ke mode standby (LED hijau berkedip) sampai dapat data lagi
    sysState = SYS_WAITING_RPM;
    waitBlinkState = false;
    lastWaitBlinkToggle = millis();
    logBoth("[RX] Sinyal J1939 hilang -> kembali ke mode standby (LED hijau berkedip)");
  }

  // ---- mode standby: nunggu RPM (pertama kali atau setelah sinyal hilang) ----
  if (sysState == SYS_WAITING_RPM) {
    if (millis() - lastWaitBlinkToggle >= WAIT_BLINK_INTERVAL_MS) {
      lastWaitBlinkToggle = millis();
      waitBlinkState = !waitBlinkState;
    }
    setOutputs(false, false, waitBlinkState); // buzzer off, merah off, hijau kedip
    return;
  }

  // ---- dari sini sysState == SYS_NORMAL ----

  // ---- tentuin zona RPM ----
  RpmZone newZone;
  if (lastRpm > RPM_DANGER)          newZone = ZONE_DANGER;
  else if (lastRpm >= RPM_CAUTION_LOW) newZone = ZONE_CAUTION;
  else                                 newZone = ZONE_SAFE;

  if (newZone != currentZone) {
    currentZone = newZone;
    blinkState = false;
    lastBlinkToggle = millis();
    const char* zoneName = currentZone == ZONE_SAFE ? "SAFE" :
                            currentZone == ZONE_CAUTION ? "CAUTION" : "DANGER";
    logBoth(String("[RX] Zona berubah -> ") + zoneName);
  }

  // ---- drive output sesuai zona ----
  switch (currentZone) {
    case ZONE_SAFE:
      setOutputs(false, false, true); // buzzer off, merah off, hijau nyala
      break;

    case ZONE_CAUTION:
      if (millis() - lastBlinkToggle >= BLINK_INTERVAL_MS) {
        lastBlinkToggle = millis();
        blinkState = !blinkState;
      }
      setOutputs(blinkState, blinkState, !blinkState);
      break;

    case ZONE_DANGER:
      setOutputs(true, false, true); // buzzer full nyala, merah off, hijau nyala
      break;
  }
}

void setOutputs(bool buzzer, bool ledRed, bool ledGreen) {
  digitalWrite(PIN_BUZZER_EXT, !buzzer);
  digitalWrite(PIN_BUZZER_INT, !buzzer);
  digitalWrite(PIN_LED_RED, !ledRed);
  digitalWrite(PIN_LED_GREEN, !ledGreen);
}

void allOff() {
  setOutputs(false, false, false);
}

void blinkError() {
  digitalWrite(PIN_LED_RED, !digitalRead(PIN_LED_RED));
  delay(300);
}

void logBoth(const String &s) {
  Serial.println(s);
  Serial2.println(s);
}