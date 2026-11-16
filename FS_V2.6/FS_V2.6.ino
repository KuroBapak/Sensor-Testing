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
  hw_stabil_init();  // pelindung reset (lihat hw_stabil.h)

  Serial1.begin(9600);
  Serial1.setRx(PA10);
  Serial1.setTx(PA9);
  Wire.setSDA(PB11);
  Wire.setSCL(PB10);
  eeprom_setup();
  sem = xSemaphoreCreateCounting(1, 0);
  i2c_lock_init();  // kunci bus I2C (lihat i2c_lock.h)
  // Now set up two Tasks to run independently.
  xTaskCreate(TaskAnalog, "TaskAnalog", STACK_ANALOG, NULL, PRIO_ANALOG, &analog);
  xTaskCreate(TaskDisplay, "TaskDisplay", STACK_DISPLAY, NULL, PRIO_DISPLAY, &display);
  xTaskCreate(TaskInput, "TaskInput", STACK_INPUT, NULL, PRIO_INPUT, &input);
  xTaskCreate(TaskControl, "blink", STACK_CONTROL, NULL, PRIO_CONTROL, &control);
  xTaskCreate(TaskDebug, "monitor", STACK_DEBUG, NULL, PRIO_DEBUG, &monitor);
  xTaskCreate(TaskLogic, "TaskLogic", STACK_LOGIC, NULL, PRIO_LOGIC, &logic);

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
