/*
 * @file    Fire Suppresion System 
 * @author  Muhammad Raka Saputra for Fire Suppresion.
 * @version 2.6 
 * @update  1. Perubahan delay di analog input
            2. Penyesuianan stake memory di thread
 * @issue   1. Panel Stuck saat dinyalakan selama 10 jam
 */


// LIST PANEL UNIT
#define BTR_1050     0    
#define BTR_2000     1   
#define BTR_2019     2   
#define BTR_2025     3    
// PANEL UNIT SETTING

#define PANEL_UNIT   BTR_1050
#define VERSI_PROGRAM "               V2.6"
#define NAMA_PANEL    " IGNIPRO-02        "
#define NAMA_MAKER    " MAKER : PT.TRISSAN"

#define VOLTAGE_DISCONNECT_MAX  0.1f
#define VOLTAGE_NORMAL_MIN      0.1f
#define VOLTAGE_NORMAL_MAX      0.75f
#define VOLTAGE_SHORT_MIN       1.1f


#define STACK_ANALOG  (configMINIMAL_STACK_SIZE + 128) // SAFETY #1 — Peak 150, margin 106 (41%) — deteksi api
#define STACK_DISPLAY (configMINIMAL_STACK_SIZE + 220) // NON-CRIT — Peak 268, margin 80 (23%) — LCD info
#define STACK_CONTROL (configMINIMAL_STACK_SIZE + 50)  // SAFETY #3 — Peak 23,  margin 155 (87%) — output fisik
#define STACK_LOGIC   (configMINIMAL_STACK_SIZE + 50)  // SAFETY #2 — Peak 50,  margin 128 (72%) — alarm sequence
#define STACK_INPUT   (configMINIMAL_STACK_SIZE + 128) // HIGH     — Peak 146, margin 110 (43%) — tombol+sensor
#define STACK_DEBUG   (configMINIMAL_STACK_SIZE + 192) // NON-CRIT — Peak 264, margin 56 (17%) — serial debug

