void TaskAnalog(void *pvParameters __attribute__((unused)))  // This is a Task.
{
  heartbeat_analog = true;  // IWDG: inisialisasi di bawah dibatasi waktu, bukan hang
  bool i2c_got = i2c_lock(I2C_LOCK_MS_BOOT);  // seluruh inisialisasi I2C memegang kunci
  // RTC yang tidak ada/rusak TIDAK boleh menghentikan deteksi api: coba beberapa kali, lalu lanjut tanpa RTC.
  for (uint8_t coba = 0; coba < RTC_INIT_COBA && !rtc_ok; coba++) {
    rtc_ok = rtc_try_init();
    if (!rtc_ok) vTaskDelay(100);
  }
  getTime();
  boot_epoch = epoch;  // Simpan waktu boot sekali saja (dari RTC, atau waktu pengganti bila RTC tidak ada)
  adc.init();
  adc.setConvRate(ADS1115_250_SPS);
  adc.setVoltageRange_mV(ADS1115_RANGE_6144);  // comment line/change parameter to change range
  adc.setCompareChannels(ADS1115_COMP_0_GND);  // comment line/change parameter to change channel
  adc.setMeasureMode(ADS1115_CONTINUOUS);      // comment line/change parameter to change mode
  if (i2c_got) i2c_unlock();
  uint8_t rtc_retry = 0;
  for (;;)                                   // A Task shall never return or exit.
  {
    heartbeat_analog = true;  // IWDG: deteksi api masih berjalan
    while (wait_asecond) vTaskDelay(100);
    if (!rtc_ok && ++rtc_retry >= RTC_RETRY_LOOP) {  // RTC belum ada: coba lagi berkala, siapa tahu sudah pulih
      rtc_retry = 0;
      bool got = i2c_lock(I2C_LOCK_MS_SENSOR);
      rtc_ok = rtc_try_init();
      if (got) i2c_unlock();
      if (rtc_ok) {
        getTime();
        boot_epoch = epoch - millis() / 1000;  // uptime tetap benar setelah waktu pindah dari pengganti ke RTC
      }
    }
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
  bool got = i2c_lock(I2C_LOCK_MS_SENSOR);  // tetap baca walau kunci tidak didapat: deteksi tidak boleh berhenti
  adc.setCompareChannels(channel);
  voltage = adc.getResult_V();  // alternative: getResult_mV for Millivolt
  if (got) i2c_unlock();
  return voltage;
}

// Inisialisasi RTC. true bila RTC terdeteksi. Pemanggil memegang kunci I2C.
bool rtc_try_init() {
  if (!rtc.begin()) return false;
  if (rtc.lostPower()) {
    rtc.adjust(DateTime(F(__DATE__), F(__TIME__)));
  }
  return true;
}

int getTime() {
  if (rtc_ok) {
    bool got = i2c_lock(I2C_LOCK_MS_SENSOR);
    kiw = rtc.now();
    if (got) i2c_unlock();
    epoch = kiw.unixtime();
  } else {
    // Tanpa RTC: waktu compile + uptime. Tidak akurat, tetapi log tetap berurutan dan panel tetap jalan.
    epoch = DateTime(F(__DATE__), F(__TIME__)).unixtime() + millis() / 1000;
  }
  time_t rawtime = epoch;
  ts = *localtime(&rawtime);
  strftime(buf, sizeof(buf), "%d/%m/%Y     %H:%M", &ts);
  return epoch;
}
