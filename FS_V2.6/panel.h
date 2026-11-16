/*
 * @file    Fire Suppresion System 
 * @author  Muhammad Raka Saputra for Fire Suppresion.
 * @version 2.6 
 * @update  1. Perubahan delay di analog input
            2. Penyesuianan stake memory di thread
 * @issue   1. Panel Stuck saat dinyalakan selama 10 jam
 */


// PANEL UNIT SETTING
// Nomor unit: tepat 3 digit, "000" sampai "999". Tampil sebagai IGNIPRO_xxx
#define NO_UNIT_PANEL "001"

// Teks splash saat booting (maks 20 karakter per baris)
#define VERSI_PROGRAM "               V2.6"
#define NAMA_PANEL    " IGNIPRO-V2        "
#define NAMA_MAKER    " MAKER : PT.TRISSAN"

#define VOLTAGE_DISCONNECT_MAX  0.1f
#define VOLTAGE_NORMAL_MIN      0.1f
#define VOLTAGE_NORMAL_MAX      0.75f
#define VOLTAGE_SHORT_MIN       1.1f


#define STACK_ANALOG  (configMINIMAL_STACK_SIZE + 128) // SAFETY #1 — Peak 150, margin 106 (41%) — deteksi api
#define STACK_DISPLAY (configMINIMAL_STACK_SIZE + 252) // NON-CRIT — Peak 268, margin 112 (29%) — LCD info (+32 untuk kunci I2C)
#define STACK_CONTROL (configMINIMAL_STACK_SIZE - 32)  // SAFETY #3 — Peak 26,  margin 70 (73%) — output fisik
#define STACK_LOGIC   (configMINIMAL_STACK_SIZE + 50)  // SAFETY #2 — Peak 50,  margin 128 (72%) — alarm sequence
#define STACK_INPUT   (configMINIMAL_STACK_SIZE + 128) // HIGH     — Peak 146, margin 110 (43%) — tombol+sensor
#define STACK_DEBUG   (configMINIMAL_STACK_SIZE + 256) // NON-CRIT — Peak 268, margin 116 (30%) — serial debug


// PRIORITAS TASK FreeRTOS (angka lebih besar = lebih tinggi; maksimum 6 karena configMAX_PRIORITIES = 7).
// TaskLogic paling tinggi: dialah yang menjalankan urutan alarm dan menggerakkan alarm + detonator
// (alarm_output). Ia tidak boleh tertunda oleh LCD, debug serial, atau task lain. Prioritas task lain
// tidak diubah dari versi sebelumnya.
#define PRIO_LOGIC    6
#define PRIO_DEBUG    5
#define PRIO_DISPLAY  3
#define PRIO_INPUT    2
#define PRIO_ANALOG   1
#define PRIO_CONTROL  0