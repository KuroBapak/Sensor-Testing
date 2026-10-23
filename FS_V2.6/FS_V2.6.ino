// pekerjaan :
// setting nama bluetooth

#include <setting.h>
#include <panel.h>

TaskHandle_t analog;
TaskHandle_t display;
TaskHandle_t input;
TaskHandle_t control;
TaskHandle_t logic;
TaskHandle_t monitor;

// the setup function runs once when you press reset or power the board
void setup()
{

  Serial1.begin(9600);
  Serial1.setRx(PA10);
  Serial1.setTx(PA9);
  Wire.setSDA(PB11);
  Wire.setSCL(PB10);
  eeprom_setup();
  sem = xSemaphoreCreateCounting(1, 0);
  // Now set up two Tasks to run independently.
  xTaskCreate(TaskAnalog, "TaskAnalog", STACK_ANALOG, NULL, 1, &analog);
  xTaskCreate(TaskDisplay, "TaskDisplay", STACK_DISPLAY, NULL, 3, &display);
  xTaskCreate(TaskInput, "TaskInput", STACK_INPUT, NULL, 2, &input);
  xTaskCreate(TaskControl, "blink", STACK_CONTROL, NULL, 0, &control);
  xTaskCreate(TaskDebug, "monitor", STACK_DEBUG, NULL, 5, &monitor);
  xTaskCreate(TaskLogic, "TaskLogic", STACK_LOGIC, NULL, 2, &logic);

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
