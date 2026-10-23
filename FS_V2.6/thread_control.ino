void TaskControl(void *pvParameters __attribute__((unused))) // This is a Task.
{
  while (setup_lcd < 1) {
    vTaskDelay(100); // one tick delay (15ms) in between reads for stability
  }
  setup_control();
  for (;;) // A Task shall never return or exit.
  {
    heartbeat_control = true;  // IWDG: kontrol output masih berjalan
    while (wait_asecond)
      vTaskDelay(100);
    loop_control();
    vTaskDelay(250); // one tick delay (15ms) in between reads for stability
  }
}

void setup_control() {
  // initialize digital pin LED_BUILTIN as an output.
  for (int iii = 0; iii < 5; iii++) {
    pinMode(DO_LED[iii], OUTPUT);
    digitalWrite(DO_LED[iii],
                 HIGH); // turn the LED on (HIGH is the voltage level)
  }
  for (int iii = 0; iii < 6; iii++) {
    pinMode(DO_ISO[iii], OUTPUT);
    digitalWrite(DO_ISO[iii],
                 HIGH); // turn the LED on (HIGH is the voltage level)
  }
}

void loop_control() {

  led_pressure();
  digitalToggle(DO_6);
}

void led_pressure(void) {
  if (STATUS_PRESSURE == 0) {
    digitalWrite(DO_CYL_1, PRESSURE_1);
    digitalWrite(DO_CYL_2, PRESSURE_2);
  } else if (STATUS_PRESSURE == 1) {
    digitalToggle(DO_CYL_1);
    digitalWrite(DO_CYL_2, PRESSURE_2);
  } else if (STATUS_PRESSURE == 2) {
    digitalToggle(DO_CYL_2);
    digitalWrite(DO_CYL_1, PRESSURE_1);
  } else if (STATUS_PRESSURE == 3) {
    digitalToggle(DO_CYL_1);
    digitalToggle(DO_CYL_2);
  }
}

void alarm_output(char number) {
  bool DETONATOR_1_ = number & 1;
  bool DETONATOR_2_ = (number >> 1) & 1;
  bool ALARM_ = (number >> 2) & 1;
  bool DISCHARGE_ = (number >> 3) & 1;

  digitalWrite(DO_DETONATOR_1, DETONATOR_1_); // JIKA NOL MAKA AKTIF
  digitalWrite(DO_DETONATOR_2, DETONATOR_2_); // JIKA NOL MAKA AKTIF
  digitalWrite(DO_DISCHARGE, DISCHARGE_);     // JIKA NOL MAKA AKTIF
  digitalWrite(DO_ALARM, ALARM_);             // JIKA NOL MAKA AKTIF
  if (ALARM_)
    digitalWrite(DO_FAULT, ALARM_); // JIKA NOL MAKA AKTIF
  else
    digitalToggle(DO_FAULT); // JIKA NOL MAKA AKTIF
}

void LED_toggle() {
  for (int iii = 0; iii < 5; iii++) {
    digitalToggle(DO_LED[iii]); // turn the LED on (HIGH is the voltage level)
    vTaskDelay(50); // one tick delay (15ms) in between reads for stability
  }
}