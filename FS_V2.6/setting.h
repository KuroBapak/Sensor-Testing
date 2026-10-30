#include "RTClib.h"
#include <ADS1115_WE.h>
#include <BobaBlox.h>
#include <EEPROM.h>
#include <LiquidCrystal_I2C.h>
#include <STM32FreeRTOS.h>
#include <Wire.h>
#include <IWatchdog.h>

bool rtc_flag;
// Struct untuk konfigurasi
typedef struct {
  bool mode_operasi;
  bool resistor;
  int delay_warning; // delay dalam detik
  int delay_release; // delay dalam detik
  char alarm_state;  // 2 = LHD || 3 = MANUAL RELEASE
  char alarm_count;  // max 6

} Settingan;

Settingan config;

// Ini akan dipanggil otomatis saat stack overflow terdeteksi
extern "C" void __attribute__((weak))
vApplicationStackOverflowHook(TaskHandle_t xTask, char *pcTaskName) {
  Serial1.println("STACK OVERFLOW DETECTED!");
  Serial1.print("Task: ");
  Serial1.println(pcTaskName);

  // Freeze untuk debug
  while (1) {
    Serial1.print("entot");
    vTaskDelay(250);
  }
}

#define I2C_ADDRESS 0x48
ADS1115_WE adc = ADS1115_WE(I2C_ADDRESS);
float voltage = 0.0;
char LHD;
// true = LHD sudah terbakar (siklus pelepasan selesai) & tersimpan di EEPROM.
// Selama true dan LHD masih SHORT, panel TIDAK alarm lagi, hanya LED fault kedip.
volatile bool lhd_burned = false;
bool CALLBUTTON = 1, PRESSURE_1, PRESSURE_2;
bool ALARM_OUTPUT, DETONATOR_1, DETONATOR_2;
char CYCLE;

SemaphoreHandle_t sem;

// ── IWDG Supervisor Heartbeat Flags ──
// Setiap task kritis set flag=true setiap loop.
// Supervisor (TaskDebug) hanya feed watchdog jika SEMUA flag true.
volatile bool heartbeat_analog  = false;
volatile bool heartbeat_logic   = false;
volatile bool heartbeat_control = false;
volatile bool heartbeat_input   = false;

unsigned char STATE_PROCCES = 1;
unsigned char STATUS_CYLINDER = 0;
unsigned char STATUS_LHD = 0;
char STATUS_PRESSURE = 0;

#define LL 1
#define L 2
#define N 0
#define H 3
#define HH 4

#define EDIT_DELAY_WARNING 1
#define EDIT_DELAY_RELEASE 2

#define LHD_NORMAL 0
#define LHD_SHORT 2
#define LHD_DISCONNECT 1

#define PRESSURE_LOW 1
#define PRESSURE_NORMAL 0

#define DO_DISCHARGE PB15
#define DO_CYL_1 PA12
#define DO_CYL_2 PA11
#define DO_FAULT PA8

#define DO_ALARM PB14
#define DO_2 PC13
#define DO_DETONATOR_1 PB5
#define DO_DETONATOR_2 PB6
#define DO_5 PC13
#define DO_6 PC13

#define DI_BTN_1 PA1  // UP
#define DI_BTN_2 PA0  // DOWN
#define DI_BTN_3 PC15 // RESET
#define DI_BTN_4 PA2  // INFO

#define DI_CALLBUTTON PB7 // Callbutton
#define DI_PRESSURE_1 PB8
#define DI_PRESSURE_2 PB9
#define DI_4 PC13

const char *CYCLE_STATUS[] = {
    "> OFF ",               // 0
    "> STEADY            ", // 1
    "> WARNING LHD       ", // 2
    "> WARNING CALLBUTTON", // 3
    "> ALARM LHD         ", // 4
    "> ALARM CALLBUTTON  ", // 5
    "> ALARM LHD         ", // 6
    "> ALARM CALLBUTTON  ", // 7
    "> RESET ALARM       ", // 8
    "> RESET LOGGER      "  // 9
};

const char *CYLINDER_STATUS[] = {
    "                    ", // 0
    "  CYLINDER 1 : ON   ", // 1
    "  CYLINDER 2 : ON   ", // 2
    "  CYLINDER 1 : OFF  ", // 3
    "  CYLINDER 1 : OFF  ", // 4
    "  PRESS RESET BUTTON"  // 5
};

const char *PRESSURE_STATUS[] = {
    "                    ", // 0
    "  CYLINDER 1 EMPTY  ", // 5
    "  CYLINDER 2 EMPTY  ", // 6
    "CYLINDER 1 & 2 EMPTY", // 7
};

const char *LHD_STATUS[] = {
    "                    ", // 0
    "LHD TIDAK TERHUBUNG ", // 1
    "LHD SHORT           ", // 2 (LHD terbakar)
};

const char *ALARM_STATE[] = {
    "   ",
    "   ",
    "LHD", // 0
    "MAN", // 1
};

char DI_BTN[4] = {DI_BTN_1, DI_BTN_2, DI_BTN_3, DI_BTN_4};
char DI[4] = {DI_CALLBUTTON, DI_PRESSURE_1, DI_PRESSURE_2, DI_4};
// bool RESET = 0;
#define ALARM_OFF 0
#define STEADY 1
#define WARNING_LHD 2
#define WARNING_CALLBUTTON 3
#define RELEASE_LHD 4
#define RELEASE_CALLBUTTON 5
#define END_CYCLE_LHD 6
#define END_CYCLE_CALLBUTTON 7
#define RESET_BUTTON 8

#define LCD_MENU_UTAMA 0
#define LCD_MENU_SETTING 1
#define LCD_MENU_1_UNIT_INFO 2
#define LCD_MENU_2_SETTING_PARAMETER 3
#define LCD_ALARM_LOGGING 10

#define STEADY_OUTPUT 0b1111
#define WARNING_OUTPUT 0b1011
#define ALARM_DET_OFF_OUTPUT 0b0011
#define ALARM_DET_1_ON_OUTPUT 0b0010
#define ALARM_DET_2_ON_OUTPUT 0b0001
#define ALARM_DOWN_OUTPUT 0b0011

#define MAX_INDEX 9

static char EPPROM_ALARM_EPOCH[10] = {0, 4, 8, 12, 16, 20, 24, 28, 32, 36};
static char EPPROM_ALARM_STATE[10] = {51, 52, 53, 54, 55, 56, 57, 58, 59, 60};
#define EPPROM_ALARM_COUNT 66
#define EPPROM_DELAY_WARNING 68
#define EPPROM_DELAY_RELEASE 69
#define EPPROM_LHD_BURNED 70
#define LHD_REARM_PASS 20 // LHD harus NORMAL 20 putaran TaskLogic (~5 detik) baru flag dihapus

bool setup_lcd = 0;

// Declarations
Button UP(DI_BTN[0]);
Button DOWN(DI_BTN[1]);
Button RESET_BTN(DI_BTN[2]);
Button INFO(DI_BTN[3]);

Button DIN_CALLBUTTON(DI[0]);
Button DIN_PRESSURE_1(DI[1]);
Button DIN_PRESSURE_2(DI[2]);
Button DIN_4(DI[3]);

RTC_DS3231 rtc;
DateTime kiw;
time_t now;
struct tm ts;
char buf[21];
char date_log[10][20];
char state_log[10];
uint32_t epoch;
uint32_t boot_epoch;

int waktu_warning = 0;

bool wait_asecond;
LiquidCrystal_I2C
    lcd(0x27, 20,
        4); // set the LCD address to 0x27 for a 16 chars and 2 line display

char DO_ISO[6] = {DO_ALARM, DO_2, DO_DETONATOR_1, DO_DETONATOR_2, DO_5, DO_6};
char DO_LED[5] = {PC13, DO_DISCHARGE, DO_CYL_1, DO_CYL_2, DO_FAULT};

// define two Tasks for DigitalRead & AnalogRead
void TaskInput(void *pvParameters);
void TaskOutput(void *pvParameters);
void TaskDisplay(void *pvParameters);
void TaskDebug(void *pvParameters);
void TaskAnalog(void *pvParameters);

char menu_state;
char page_state;
char status_state;
char cursor_state;
char cursor_alarm_state;

char edit_state;

char mode_operation;

// Enum untuk mode operasi
typedef enum { MODE_MANUAL, MODE_AUTO } Mode;

// Enum untuk jenis resistor
typedef enum {
  RESISTOR_4K7, // 4700 ohm
  RESISTOR_10K  // 10000 ohm
} ResistorType;

#define AKTIF 0
#define NON_AKTIF 1