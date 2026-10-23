void TaskAnalog(void *pvParameters __attribute__((unused)))  // This is a Task.
{
init_init:
  // initialize digital LED_BUILTIN on pin 13 as an output.
  if (!rtc.begin()) {
    while (1) {
      vTaskDelay(10);  // one tick delay (15ms) in between reads for stability
      Serial.flush();
    }
  }
  if (rtc.lostPower()) {
    rtc.adjust(DateTime(F(__DATE__), F(__TIME__)));
  }
  getTime();
  boot_epoch = epoch;  // Simpan waktu boot sekali saja dari RTC
  adc.init();
  adc.setConvRate(ADS1115_250_SPS);
  adc.setVoltageRange_mV(ADS1115_RANGE_6144);  // comment line/change parameter to change range
  adc.setCompareChannels(ADS1115_COMP_0_GND);  // comment line/change parameter to change channel
  adc.setMeasureMode(ADS1115_CONTINUOUS);      // comment line/change parameter to change mode
  for (;;)                                     // A Task shall never return or exit.
  {
    heartbeat_analog = true;  // IWDG: deteksi api masih berjalan
    while (wait_asecond) vTaskDelay(100);
    getTime();
    float A0_val;
    float A0_val_sementara = 0;
    for (int xxx = 0; xxx < 5; xxx++) {  // 20 *5  = 100
      A0_val = readChannel(ADS1115_COMP_0_GND);
      A0_val_sementara = A0_val_sementara + A0_val;
      vTaskDelay(100);  // one tick delay (15ms) in between reads for stability
    }
    voltage = A0_val_sementara / 5;
    // if (voltage == 0) goto init_init;
    if (voltage < VOLTAGE_DISCONNECT_MAX) LHD = LHD_DISCONNECT;
    else if (voltage <= VOLTAGE_NORMAL_MAX) LHD = LHD_NORMAL;
    else if (voltage >= VOLTAGE_SHORT_MIN) LHD = LHD_SHORT;

    vTaskDelay(10);  // one tick delay (15ms) in between reads for stability
    xSemaphoreGive(sem);
    vTaskDelay(240);  // one tick delay (15ms) in between reads for stability
    digitalToggle(DO_5);
  }
}

float readChannel(ADS1115_MUX channel) {
  float voltage = 0.0;
  adc.setCompareChannels(channel);
  voltage = adc.getResult_V();  // alternative: getResult_mV for Millivolt
  return voltage;
}

int getTime() {
  kiw = rtc.now();
  epoch = kiw.unixtime();
  time_t rawtime = epoch;
  ts = *localtime(&rawtime);
  strftime(buf, sizeof(buf), "%d/%m/%Y     %H:%M", &ts);
  return epoch;
}
