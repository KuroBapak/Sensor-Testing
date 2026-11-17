void TaskControl(void *pvParameters __attribute__((unused))) // This is a Task.
{
  // Pin output sudah diatur di setup() (outputs_safe_init), jadi tidak perlu menunggu LCD.
  for (;;) // A Task shall never return or exit.
  {
    heartbeat_control = true;  // IWDG: kontrol output masih berjalan
    while (wait_asecond)
      vTaskDelay(100);
    loop_control();
    vTaskDelay(250); // one tick delay (15ms) in between reads for stability
  }
}

void loop_control() {

  led_pressure();
  digitalToggle(DO_6);
}

// LED silinder berkedip bila tekanan silinder tidak terdeteksi ATAU detonator silinder itu tidak terdeteksi
// (DET HIGH = LOSS). Detonator 1 -> LED silinder 1, detonator 2 -> LED silinder 2.
// Hanya LED yang berubah; STATUS_PRESSURE dan teks LCD tekanan tidak terpengaruh.
void led_pressure(void) {
  bool blink_1 = PRESSURE_1 || (digitalRead(DI_DET_1) == HIGH);
  bool blink_2 = PRESSURE_2 || (digitalRead(DI_DET_2) == HIGH);

  if (blink_1) digitalToggle(DO_CYL_1);
  else         digitalWrite(DO_CYL_1, PRESSURE_1);
  if (blink_2) digitalToggle(DO_CYL_2);
  else         digitalWrite(DO_CYL_2, PRESSURE_2);
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
    fault_led_steady();
  else
    digitalToggle(DO_FAULT); // JIKA NOL MAKA AKTIF
}

// Kondisi tanpa alarm: LED fault kedip jika LHD disconnect atau LHD terbakar (SHORT), selain itu normal.
// Dipanggil dari alarm_output (TaskLogic) ~tiap 250 ms, jadi toggle = kedip, tanpa delay.
void fault_led_steady(void) {
  if (LHD == LHD_DISCONNECT || (lhd_burned && LHD == LHD_SHORT))
    digitalToggle(DO_FAULT);
  else
    digitalWrite(DO_FAULT, HIGH);
}

