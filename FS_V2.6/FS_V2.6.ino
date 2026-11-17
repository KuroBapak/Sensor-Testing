// pekerjaan :
// setting nama bluetooth

#include <setting.h>
#include <panel.h>
#include <hw_stabil.h>

TaskHandle_t analog;
TaskHandle_t display;
TaskHandle_t input;
TaskHandle_t control;
TaskHandle_t logic;
TaskHandle_t monitor;

// the setup function runs once when you press reset or power the board
void setup()
{
  // IWDG dimulai PALING AWAL, sebelum inisialisasi apa pun, supaya hang saat boot (LCD, I2C, EEPROM)
  // juga tertangkap. Setelah ini watchdog TIDAK BISA dihentikan. Sampai TaskWdg berjalan (puluhan ms)
  // timeout 8 detik cukup. Penyebab reset dibaca dulu, lalu dicetak oleh TaskDebug.
  wdg_was_reset = IWatchdog.isReset(true);
  IWatchdog.begin(WDG_TIMEOUT_US);

  hw_stabil_init();  // jam HAL TIM4 (lihat hw_stabil.h)
  outputs_safe_init();  // pin output aman sejak awal boot, tidak menunggu LCD

  Serial1.begin(9600);
  Serial1.setRx(PA10);
  Serial1.setTx(PA9);
  Wire.setSDA(PB11);
  Wire.setSCL(PB10);
  eeprom_setup();
  sem = xSemaphoreCreateCounting(1, 0);
  i2c_lock_init();  // kunci bus I2C (lihat i2c_lock.h)
  // Task kritis dibuat lebih dulu: jika heap habis, yang gagal dibuat adalah task non-kritis, bukan logika alarm.
  // Task yang gagal dibuat tidak pernah menandai hidup, sehingga TaskWdg berhenti memberi makan IWDG
  // dan panel reset (bukan menyala tanpa task itu).
  xTaskCreate(TaskLogic, "TaskLogic", STACK_LOGIC, NULL, PRIO_LOGIC, &logic);
  xTaskCreate(TaskWdg, "wdg", STACK_WDG, NULL, PRIO_WDG, NULL);
  xTaskCreate(TaskAnalog, "TaskAnalog", STACK_ANALOG, NULL, PRIO_ANALOG, &analog);
  xTaskCreate(TaskInput, "TaskInput", STACK_INPUT, NULL, PRIO_INPUT, &input);
  xTaskCreate(TaskControl, "blink", STACK_CONTROL, NULL, PRIO_CONTROL, &control);
  xTaskCreate(TaskDisplay, "TaskDisplay", STACK_DISPLAY, NULL, PRIO_DISPLAY, &display);
  xTaskCreate(TaskDebug, "monitor", STACK_DEBUG, NULL, PRIO_DEBUG, &monitor);

  // start scheduler
  vTaskStartScheduler();
  Serial1.println("Insufficient RAM");
  while (1)
    ;
}

void loop()
{
  // Empty. Things are done in Tasks.
}
