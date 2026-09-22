#include "main.h"
#include "mcp2515.h"

#define CS_PORT GPIOA
#define CS_PIN  GPIO_PIN_4

void Setup_Simulator(void) {
    MCP2515_Reset();
    MCP2515_SetBitrate(CAN_250KBPS, MCP_8MHZ);
    MCP2515_SetMode(MCP_NORMAL);
}

void J1939_Broadcast_RPM(float target_rpm) {
    // 0x0CF00400 Breakdown J1939: 
    // Priority = 3 (0x0C...)
    // PGN = 61444 (...F004...)
    // Source Address = 00 Engine (...00)
    uint32_t tx_id = 0x0CF00400; 
    uint8_t tx_data[8];

    // Resolusi 0.125 RPM per bit, jadi nilai target dikali 8
    uint16_t raw_rpm = (uint16_t)(target_rpm * 8.0f);

    tx_data[0] = 0xFF; // Engine Torque
    tx_data[1] = 0xFF; // Extended Torque
    tx_data[2] = 0xFF; // Engine Speed At Idle
    
    // Byte J1939 RPM
    tx_data[3] = (uint8_t)(raw_rpm & 0xFF);        // LSB
    tx_data[4] = (uint8_t)((raw_rpm >> 8) & 0xFF); // MSB
    
    tx_data[5] = 0xFF; // Starter Mode dll
    tx_data[6] = 0xFF;
    tx_data[7] = 0xFF;

    // Parameter '1' artinya kirim format Extended ID 29-bit
    MCP2515_SendMessage(tx_id, 8, tx_data, 1);
}

int main(void) {
    HAL_Init();
    // Pastikan SPI1 dan GPIO Port A di-generate dari STM32CubeMX
    
    Setup_Simulator();
    
    while (1) {
        // Skenario 1: RPM Normal (Aman) - LED Hijau Receiver Nyala
        for(int i = 0; i < 100; i++) {
            J1939_Broadcast_RPM(1200.0f);
            HAL_Delay(50); // Broadcast 20Hz (Standar Alat Berat)
        }
        
        // Skenario 2: RPM Drop / Engine Stall (Bahaya) - LED Merah & Buzzer Nyala
        for(int i = 0; i < 100; i++) {
            J1939_Broadcast_RPM(250.0f);
            HAL_Delay(50);
        }
    }
}