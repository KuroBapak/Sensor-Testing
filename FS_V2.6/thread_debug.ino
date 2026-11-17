

char btBuffer[32];
uint8_t btIndex = 0;

// --- Debug Channel Bitmask ---
#define DBG_STACK   0x0001
#define DBG_STATE   0x0002
#define DBG_TIME    0x0004
#define DBG_MENU    0x0008
#define DBG_ANALOG  0x0010
#define DBG_DIN     0x0020
#define DBG_DET     0x0040
#define DBG_BTN     0x0080
#define DBG_DELAY   0x0100
#define DBG_ALL     0x01FF

uint16_t debug_mask = DBG_ANALOG;  // Default: hanya ANALOG yang nyala
bool debug_mute = false;           // Mute output tanpa mengubah channel

struct DebugChannel {
  const char* name;
  uint16_t bit;
};

const DebugChannel dbg_channels[] = {
  {"STACK",  DBG_STACK},
  {"STATE",  DBG_STATE},
  {"TIME",   DBG_TIME},
  {"MENU",   DBG_MENU},
  {"ANALOG", DBG_ANALOG},
  {"DIN",    DBG_DIN},
  {"DET",    DBG_DET},
  {"BTN",    DBG_BTN},
  {"DELAY",  DBG_DELAY},
  {"ALL",    DBG_ALL},
};
const uint8_t DBG_CH_COUNT = sizeof(dbg_channels) / sizeof(dbg_channels[0]);


void processCommand(const char* cmd, Print* port) {
  // Parse command (Contoh: "SET WARN 15")
  if (strncmp(cmd, "SET WARN ", 9) == 0) {
    int val = atoi(cmd + 9);
    if (val >= 0 && val <= 255) {
      config.delay_warning = val;
      write_delay_warning();
      port->printf("OK: Delay Warning set to %d s\r\n", val);
    }
  } 
  // Parse command (Contoh: "SET REL 5")
  else if (strncmp(cmd, "SET REL ", 8) == 0) {
    int val = atoi(cmd + 8);
    if (val >= 0 && val <= 255) {
      config.delay_release = val;
      write_delay_release();
      port->printf("OK: Delay Release set to %d s\r\n", val);
    }
  }
  // Parse command: "GET INFO"
  else if (strcmp(cmd, "GET INFO") == 0) {
    port->printf("WARN=%d s, REL=%d s\r\n", config.delay_warning, config.delay_release);
    port->printf("DEBUG: %d\r\n", debug_mute ? 0 : 1);
    for (uint8_t i = 0; i < DBG_CH_COUNT - 1; i++) {
      port->printf("  %-7s: %d\r\n", dbg_channels[i].name, (debug_mask & dbg_channels[i].bit) ? 1 : 0);
    }
  }
  // Parse command: "UPTIME"
  else if (strcmp(cmd, "UPTIME") == 0) {
    uint32_t uptime_sec = epoch - boot_epoch;
    uint32_t days  = uptime_sec / 86400;
    uint8_t  hours = (uptime_sec % 86400) / 3600;
    uint8_t  mins  = (uptime_sec % 3600) / 60;
    uint8_t  secs  = uptime_sec % 60;
    port->printf("UPTIME: %lu d %02d:%02d:%02d\r\n", days, hours, mins, secs);
  }
  // Parse command: "DEBUG 0" (mute) atau "DEBUG 1" (unmute)
  else if (strncmp(cmd, "DEBUG ", 6) == 0) {
    int val = atoi(cmd + 6);
    debug_mute = (val == 0);
    port->printf("OK: DEBUG %d\r\n", val ? 1 : 0);
  }
  else {
    // Cek apakah command cocok dengan nama channel (Contoh: "ANALOG 1", "STATE 0")
    bool found = false;
    for (uint8_t i = 0; i < DBG_CH_COUNT; i++) {
      uint8_t len = strlen(dbg_channels[i].name);
      if (strncmp(cmd, dbg_channels[i].name, len) == 0 && cmd[len] == ' ') {
        int val = atoi(cmd + len + 1);
        if (val) debug_mask |= dbg_channels[i].bit;
        else     debug_mask &= ~dbg_channels[i].bit;
        port->printf("OK: %s %d\r\n", dbg_channels[i].name, val ? 1 : 0);
        found = true;
        break;
      }
    }
    if (!found) port->println("ERROR: Unknown Command");
  }
}

void readSerialCommands() {
  // --- Baca dari Bluetooth (Serial1). "Serial" adalah alias Serial1 di board ini, jadi satu jalur cukup. ---
  while (Serial1.available() > 0) {
    char c = Serial1.read();
    if (c == '\r') continue; // Abaikan carriage return
    if (c == '\n') {
      btBuffer[btIndex] = '\0';
      if (btIndex > 0) processCommand(btBuffer, &Serial1);
      btIndex = 0;
    } else if (btIndex < sizeof(btBuffer) - 1) {
      btBuffer[btIndex++] = c;
    }
  }
}

void TaskDebug(void* pvParameters __attribute__((unused)))  // This is a Task.
{
  // IWDG sudah dimulai paling awal di setup() dan diberi makan oleh TaskWdg (thread_wdg.ino).
  // Task ini hanya melaporkan lewat Bluetooth.
  if (wdg_was_reset) {  // dibaca di setup() sebelum IWDG dimulai
    Serial1.println("========================================");
    Serial1.println("!! WATCHDOG RESET TERDETEKSI !!");
    Serial1.println("!! Sistem di-restart otomatis oleh IWDG");
    Serial1.println("========================================");
  }
  Serial1.println("IWDG: Watchdog aktif (timeout 8s)");

  for (;;)  // A Task shall never return or exit.
  {
    if (!debug_mute && debug_mask) {
      if (debug_mask & DBG_STACK) {
        Serial1.printf("Task Analog  | Stack sisa: %d/%d\r\n", uxTaskGetStackHighWaterMark(analog), STACK_ANALOG);
        Serial1.printf("Task display | Stack sisa: %d/%d\r\n", uxTaskGetStackHighWaterMark(display), STACK_DISPLAY);
        Serial1.printf("Task control | Stack sisa: %d/%d\r\n", uxTaskGetStackHighWaterMark(control), STACK_CONTROL);
        Serial1.printf("Task logic   | Stack sisa: %d/%d\r\n", uxTaskGetStackHighWaterMark(logic), STACK_LOGIC);
        Serial1.printf("Task input   | Stack sisa: %d/%d\r\n", uxTaskGetStackHighWaterMark(input), STACK_INPUT);
        Serial1.printf("Task debug   | Stack sisa: %d/%d\r\n", uxTaskGetStackHighWaterMark(monitor), STACK_DEBUG);
      }
      if (debug_mask & DBG_STATE) {
        Serial1.printf("state    : %s\r\n", CYCLE_STATUS[STATE_PROCCES]);
        Serial1.printf("pressure : %s\r\n", PRESSURE_STATUS[STATUS_PRESSURE]);
      }
      if (debug_mask & DBG_TIME) {
        Serial1.printf("Unix time       : %lu\r\n", epoch);
        Serial1.printf("tanggal         : %s\r\n", buf);
      }
      if (debug_mask & DBG_MENU) {
        Serial1.printf("menu_state     : %d\r\n", menu_state);
        Serial1.printf("cursor_state   : %d\r\n", cursor_state);
        Serial1.printf("edit_state      : %d\r\n", edit_state);
      }
      if (debug_mask & DBG_ANALOG) {
        Serial1.printf("analog input   : %d.%02d V\r\n", (int)voltage, (int)(voltage * 100) % 100);
      }
      if (debug_mask & DBG_DIN) {
        Serial1.printf("digital_input   : %d %d %d\r\n", CALLBUTTON, PRESSURE_1, PRESSURE_2);
      }
      if (debug_mask & DBG_DET) {
        Serial1.printf("detonator_status: %d %d\r\n", digitalRead(PA4), digitalRead(PA3));
      }
      if (debug_mask & DBG_BTN) {
        Serial1.printf("button          : %d %d %d %d\r\n", UP.state(), DOWN.state(), RESET_BTN.state(), INFO.state());
      }
      if (debug_mask & DBG_DELAY) {
        Serial1.printf("delay warning   : %d s\r\n", config.delay_warning);
        Serial1.printf("delay release   : %d s\r\n", config.delay_release);
        Serial1.printf("waktu warning   : %d\r\n", waktu_warning);
        Serial1.printf("alarm count     : %d\r\n", config.alarm_count);
      }
      Serial1.println("----------------------------------------");
    }

    // Laporan dari TaskWdg: pengecekan terakhir gagal (IWDG tidak diberi makan).
    if (wdg_fail) {
      Serial1.println("!! IWDG: HEARTBEAT MISSING !!");
      Serial1.printf("  Analog:%d Logic:%d Control:%d Input:%d\r\n",
        wdg_alive & 1, (wdg_alive >> 1) & 1, (wdg_alive >> 2) & 1, (wdg_alive >> 3) & 1);
    }

    // Loop kecil untuk membaca command dari Bluetooth
    // Memecah 5000ms menjadi 50 x 100ms agar lebih responsif terhadap input
    for (int i = 0; i < 50; i++) {
      readSerialCommands();
      vTaskDelay(100);
    }
  }
}

void read_delay_warning(void) {
  config.delay_warning = EEPROM.read(EPPROM_DELAY_WARNING);
}

void write_delay_warning(void) {
  EEPROM.update(EPPROM_DELAY_WARNING, config.delay_warning);  // simpan alamt memory
}

void read_delay_release(void) {
  config.delay_release = EEPROM.read(EPPROM_DELAY_RELEASE);
}

void write_delay_release(void) {
  EEPROM.update(EPPROM_DELAY_RELEASE, config.delay_release);  // simpan alamt memory
}

void read_alarm_count(void) {
  config.alarm_count = EEPROM.read(EPPROM_ALARM_COUNT);
}

void write_alarm_count(char counting) {
  EEPROM.update(EPPROM_ALARM_COUNT, counting);  // simpan alamt memory
}

void reset_alarm_count(void) {
  EEPROM.update(EPPROM_ALARM_COUNT, 0);  // simpan alamt memory
}

// Simpan/hapus flag LHD terbakar. Pola sama dengan write_alarm_log:
// task lain dijeda (wait_asecond) selama tulis flash, tidak ada loop tanpa batas.
// Flash hanya ditulis kalau nilainya memang berubah.
void write_lhd_burned(bool value) {
  if (lhd_burned == value) return;
  wait_asecond = true;
  vTaskDelay(250);
  lhd_burned = value;
  if (EEPROM.read(EPPROM_LHD_BURNED) != (unsigned char)value) EEPROM.update(EPPROM_LHD_BURNED, value);
  vTaskDelay(25);
  wait_asecond = false;
}

// Dipanggil tiap putaran TaskLogic (non-blocking). LHD stabil NORMAL = sudah diganti -> re-arm.
// LHD DISCONNECT / SHORT tidak menghapus flag.
void lhd_burned_watch(void) {
  static unsigned char normal_pass = 0;
  if (!lhd_burned || LHD != LHD_NORMAL) {
    normal_pass = 0;
    return;
  }
  if (normal_pass < LHD_REARM_PASS) {
    normal_pass++;
    return;
  }
  normal_pass = 0;
  write_lhd_burned(false);
}

void write_alarm_log(uint32_t epochtime, char alarm_value) {
  read_alarm_count();
  // Tulis date datalogger
  unsigned char data_write[4];
  char counting_alarm = config.alarm_count;
  counting_alarm = config.alarm_count % MAX_INDEX;
  int address_reg_date_temp = EPPROM_ALARM_EPOCH[counting_alarm];
  data_write[0] = (unsigned)epochtime >> 24;
  data_write[1] = (unsigned)epochtime >> 16;
  data_write[2] = (unsigned)epochtime >> 8;
  data_write[3] = (unsigned)epochtime;
  EEPROM.update(address_reg_date_temp++, data_write[0]);
  EEPROM.update(address_reg_date_temp++, data_write[1]);
  EEPROM.update(address_reg_date_temp++, data_write[2]);
  EEPROM.update(address_reg_date_temp++, data_write[3]);

  // Tulis STATUS ALARM
  EEPROM.update(EPPROM_ALARM_STATE[counting_alarm], alarm_value);  // simpan alarm value
  config.alarm_count++;
  write_alarm_count(config.alarm_count);
}
void read_alarm_log(bool gedebug) {
  for (int count = 0; count < 10; ++count) {
    unsigned char data_read[4];
    int address_reg_date_temp = EPPROM_ALARM_EPOCH[count];

    data_read[0] = EEPROM.read(address_reg_date_temp++);
    data_read[1] = EEPROM.read(address_reg_date_temp++);
    data_read[2] = EEPROM.read(address_reg_date_temp++);
    data_read[3] = EEPROM.read(address_reg_date_temp++);

    // baca Memory Alarm
    state_log[count] = EEPROM.read(EPPROM_ALARM_STATE[count]);  // simpan alarm value

    unsigned u = (unsigned)data_read[0] << 24 | (unsigned)data_read[1] << 16 | (unsigned)data_read[2] << 8 | data_read[3];
    int i;
    memcpy(&i, &u, sizeof i);
    time_t now = i;
    ts = *localtime(&now);
      strftime(date_log[count], sizeof(date_log[count]), "%Y-%m-%d %H:%M", &ts);
    vTaskDelay(10);  // one tick delay (15ms) in between reads for stability
  }
}

void reset_alarm_logger(void) {
  unsigned kimex = 0xffffffff;
  unsigned char data_write[4];
  data_write[0] = (unsigned)kimex >> 24;
  data_write[1] = (unsigned)kimex >> 16;
  data_write[2] = (unsigned)kimex >> 8;
  data_write[3] = (unsigned)kimex;

  for (int count = 0; count < 10; ++count) {
    int address_reg_date_temp = EPPROM_ALARM_EPOCH[count];
    EEPROM.update(EPPROM_ALARM_STATE[count], 0);  // simpan alarm value
    EEPROM.update(address_reg_date_temp++, data_write[0]);
    EEPROM.update(address_reg_date_temp++, data_write[1]);
    EEPROM.update(address_reg_date_temp++, data_write[2]);
    EEPROM.update(address_reg_date_temp++, data_write[3]);
    vTaskDelay(10);  // one tick delay (15ms) in between reads for stability
  }
  reset_alarm_count();
}


void eeprom_setup(void) {

  config.alarm_count = 0;

  read_delay_warning();
  read_delay_release();
  read_alarm_count();
  lhd_burned = (EEPROM.read(EPPROM_LHD_BURNED) == 1);  // EEPROM kosong (255) = belum pernah terbakar

  if (config.delay_release == 0 || config.delay_release == 255) config.delay_release = 2;
  if (config.delay_warning == 0 || config.delay_warning == 255) config.delay_warning = 5;
  if (config.alarm_count == 0 || config.alarm_count == 255) config.alarm_count = 0;

  write_delay_release();
  write_delay_warning();
  write_alarm_count(config.alarm_count);
}
