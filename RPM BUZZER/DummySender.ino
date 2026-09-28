#include <SPI.h>
#include <mcp_can.h>

// HW-184 (default SPI1 STM32F103: SCK=PA5, MISO=PA6, MOSI=PA7)
#define CAN_CS_PIN   PA4
#define CAN_CRYSTAL  MCP_8MHZ
#define CAN_SPEED    CAN_250KBPS
MCP_CAN CAN0(CAN_CS_PIN);

#define ENGINE_SA 0x00
const unsigned long EEC1_ID = 0x0CF00400UL | ENGINE_SA; // PGN 61444 (EEC1)

float simRPM = 2000.0;
int   direction = -1;
unsigned long lastSend = 0;

void setup() {
  Serial.begin(115200);
  if (CAN0.begin(MCP_ANY, CAN_SPEED, CAN_CRYSTAL) == CAN_OK) {
    Serial.println("[TX] MCP2515 init OK");
  } else {
    Serial.println("[TX] MCP2515 init GAGAL, cek wiring/crystal");
    while (1);
  }
  CAN0.setMode(MCP_NORMAL);
  Serial.println("[TX] Mulai broadcast simulasi EEC1 (RPM sweep 2000 <-> 400)");
}

void sendEEC1(float rpm) {
  uint16_t raw = (uint16_t)(rpm / 0.125f); // SPN190, resolusi 0.125 rpm/bit
  byte data[8];
  data[0] = 0xFF; data[1] = 0xFF; data[2] = 0xFF;
  data[3] = raw & 0xFF;
  data[4] = (raw >> 8) & 0xFF;
  data[5] = 0xFF; data[6] = 0xFF; data[7] = 0xFF;

  byte st = CAN0.sendMsgBuf(EEC1_ID, 1, 8, data);
  Serial.print("[TX] RPM="); Serial.print(rpm, 1);
  Serial.println(st == CAN_OK ? "  (sent)" : "  (FAILED)");
}

void loop() {
  if (millis() - lastSend >= 100) {
    lastSend = millis();
    sendEEC1(simRPM);
    simRPM += direction * 15.0;
    if (simRPM <= 400)  direction = 1;
    if (simRPM >= 2000) direction = -1;
  }
}