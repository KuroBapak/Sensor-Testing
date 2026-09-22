#include "main.h"
#include "mcp2515.h" // Gunakan library MCP2515 HAL STM32

// Konfigurasi Pin Berdasarkan Setup Anda
#define CS_PORT         GPIOA
#define CS_PIN          GPIO_PIN_4

#define EXT_BUZZER_PORT GPIOB
#define EXT_BUZZER_PIN  GPIO_PIN_7

#define LED_RED_PORT    GPIOB
#define LED_RED_PIN     GPIO_PIN_6

#define LED_GREEN_PORT  GPIOB
#define LED_GREEN_PIN   GPIO_PIN_5

#define INT_BUZZER_PORT GPIOB
#define INT_BUZZER_PIN  GPIO_PIN_4

// Variabel Global
float engine_rpm = 0.0f;
uint32_t last_msg_time = 0;

void Setup_Receiver(void) {
    MCP2515_Reset();
    MCP2515_SetBitrate(CAN_250KBPS, MCP_8MHZ); // Standar J1939 Alat Berat
    MCP2515_SetMode(MCP_NORMAL);
}

// Fungsi untuk mengatur aktuator (LED & Buzzer)
void Set_Alarm_State(uint8_t is_danger, uint8_t is_engine_off) {
    if (is_engine_off) {
        // Mesin mati / lost contact: Semua mati, cuma LED Merah nyala
        HAL_GPIO_WritePin(LED_GREEN_PORT, LED_GREEN_PIN, GPIO_PIN_RESET);
        HAL_GPIO_WritePin(LED_RED_PORT, LED_RED_PIN, GPIO_PIN_SET);
        HAL_GPIO_WritePin(EXT_BUZZER_PORT, EXT_BUZZER_PIN, GPIO_PIN_RESET);
        HAL_GPIO_WritePin(INT_BUZZER_PORT, INT_BUZZER_PIN, GPIO_PIN_RESET);
    } 
    else if (is_danger) {
        // Bahaya (RPM di bawah 300): Merah nyala, Buzzer bunyi
        HAL_GPIO_WritePin(LED_GREEN_PORT, LED_GREEN_PIN, GPIO_PIN_RESET);
        HAL_GPIO_WritePin(LED_RED_PORT, LED_RED_PIN, GPIO_PIN_SET);
        HAL_GPIO_WritePin(EXT_BUZZER_PORT, EXT_BUZZER_PIN, GPIO_PIN_SET);
        HAL_GPIO_WritePin(INT_BUZZER_PORT, INT_BUZZER_PIN, GPIO_PIN_SET);
    } 
    else {
        // Aman (RPM normal): Hijau nyala, Merah dan Buzzer mati
        HAL_GPIO_WritePin(LED_GREEN_PORT, LED_GREEN_PIN, GPIO_PIN_SET);
        HAL_GPIO_WritePin(LED_RED_PORT, LED_RED_PIN, GPIO_PIN_RESET);
        HAL_GPIO_WritePin(EXT_BUZZER_PORT, EXT_BUZZER_PIN, GPIO_PIN_RESET);
        HAL_GPIO_WritePin(INT_BUZZER_PORT, INT_BUZZER_PIN, GPIO_PIN_RESET);
    }
}

void Process_CAN_Receiver(void) {
    uint32_t rx_id;
    uint8_t rx_dlc;
    uint8_t rx_data[8];

    // Polling data dari modul (Sangat disarankan pakai interrupt pin INT ke depannya)
    if (MCP2515_ReadMessage(&rx_id, &rx_dlc, rx_data) == MCP_OK) {
        
        // Ekstrak PGN (Parameter Group Number) J1939
        uint32_t pgn = (rx_id >> 8) & 0x3FFFF;

        // PGN 61444 (0xF004) - Electronic Engine Controller 1
        if (pgn == 61444 || pgn == 0xF004) {
            
            // LSB Byte 4, MSB Byte 5 (Endianness standar J1939)
            uint16_t raw_rpm = (uint16_t)rx_data[3] | ((uint16_t)rx_data[4] << 8);

            // Validasi: 0xFA00 ke atas biasanya kode error/unavailable di J1939
            if (raw_rpm < 0xFA00) {
                engine_rpm = raw_rpm * 0.125f; 
                last_msg_time = HAL_GetTick(); // Reset watchdog timer
            }
        }
    }

    // --- LOGIKA KONDISI ---
    if (HAL_GetTick() - last_msg_time > 2000) {
        engine_rpm = 0.0f; // Timeout 2 detik, anggap mesin mati / kabel putus
        Set_Alarm_State(0, 1); 
    }
    else if (engine_rpm > 0.0f && engine_rpm < 300.0f) {
        Set_Alarm_State(1, 0); // Kondisi Bahaya
    } 
    else {
        Set_Alarm_State(0, 0); // Kondisi Aman
    }
}

int main(void) {
    HAL_Init();
    // PENTING: Pastikan SPI1, GPIO Port A & B sudah di-generate dari STM32CubeMX
    
    Setup_Receiver();
    
    // Matikan semua output saat awal nyala
    Set_Alarm_State(0, 1);
    
    while (1) {
        Process_CAN_Receiver();
    }
}