void TaskLogic(void *pvParameters __attribute__((unused)))  // This is a Task.
{
  for (;;)  // A Task shall never return or exit.
  {
    heartbeat_logic = true;  // IWDG: logika alarm masih berjalan
    switch (STATE_PROCCES) {

      case ALARM_OFF:
        alarm_output(STEADY_OUTPUT);
        break;
      case STEADY:
        alarm_output(STEADY_OUTPUT);
        // LHD sudah terbakar & masih SHORT = fault saja (LED kedip), jangan alarm lagi
        if (LHD == LHD_SHORT  && !lhd_burned && !menu_state) STATE_PROCCES = WARNING_LHD;
        else if (!CALLBUTTON  && !menu_state) STATE_PROCCES = WARNING_CALLBUTTON;
        break;
      case WARNING_LHD:
        wait_asecond = true;        
        vTaskDelay(250);  
        write_alarm_log(epoch, WARNING_LHD);
        vTaskDelay(25);  
        wait_asecond = false;        
        if (wait_warning_proccess(WARNING_OUTPUT)) goto reset_bro;
        // logika alarm dari LHD
        STATE_PROCCES = RELEASE_LHD;
        break;
      case WARNING_CALLBUTTON:
        wait_asecond = true;        
        vTaskDelay(250); 
        write_alarm_log(epoch, WARNING_CALLBUTTON);
        vTaskDelay(25); 
        wait_asecond = false;    
        alarm_output(WARNING_OUTPUT);
        // logika alarm dari LHD
        STATE_PROCCES = RELEASE_CALLBUTTON;
        break;
      case RELEASE_LHD:
        waktu_warning = 0;
        // aktifkan detonator 1
        // check pressure
        if (PRESSURE_1) {
          STATUS_CYLINDER = 3;  //
          if (wait_release_proccess(ALARM_DET_OFF_OUTPUT)) goto reset_bro;
          goto loncat_proses_1;
        }
        STATUS_CYLINDER = 1;
       if (wait_release_proccess(ALARM_DET_1_ON_OUTPUT)) goto reset_bro;
loncat_proses_1:
        waktu_warning = 0;
        // aktifkan detonator 1
        // check pressure
        if (PRESSURE_2) {
          STATUS_CYLINDER = 4;
                    // vTaskDelay(500); 
          if (wait_release_proccess(ALARM_DET_OFF_OUTPUT)) goto reset_bro;
          goto loncat_proses_2;
        }
        STATUS_CYLINDER = 2;
       if(wait_release_proccess(ALARM_DET_2_ON_OUTPUT)) goto reset_bro;
loncat_proses_2:
        STATE_PROCCES = END_CYCLE_LHD;
        break;

      case RELEASE_CALLBUTTON:
        waktu_warning = 0;
        // aktifkan detonator 1
        // check pressure
        if (PRESSURE_1) {
          STATUS_CYLINDER = 3;  //
          // vTaskDelay(500); 
          if (wait_release_proccess(ALARM_DET_OFF_OUTPUT)) goto reset_bro;
          goto loncat_proses_callbutton_1;
        }
        STATUS_CYLINDER = 1;
        if (wait_release_proccess(ALARM_DET_1_ON_OUTPUT)) goto reset_bro;
loncat_proses_callbutton_1:
        // aktifkan detonator 1
        // check pressure
        if (PRESSURE_2) {
          STATUS_CYLINDER = 4;
          // vTaskDelay(500); 
          if (wait_release_proccess(ALARM_DET_OFF_OUTPUT)) goto reset_bro;
          goto loncat_proses_callbutton_2;
        }
        STATUS_CYLINDER = 2;
       if (wait_release_proccess(ALARM_DET_2_ON_OUTPUT)) goto reset_bro;
loncat_proses_callbutton_2:
        STATE_PROCCES = END_CYCLE_CALLBUTTON;
        break;

      case END_CYCLE_LHD:
        // siklus selesai
        STATUS_CYLINDER = 5;
        alarm_output(ALARM_DOWN_OUTPUT);
        write_lhd_burned(true);  // LHD dianggap terbakar, tersimpan di EEPROM
        while (1) {
          heartbeat_logic = true;  // IWDG: menunggu reset button
          alarm_output(ALARM_DOWN_OUTPUT);
          if (STATE_PROCCES == RESET_BUTTON) goto reset_bro;
          vTaskDelay(100); 
        }
        break;
      case END_CYCLE_CALLBUTTON:
        // siklus selesai
        STATUS_CYLINDER = 5;
        while (1) {
          heartbeat_logic = true;  // IWDG: menunggu reset button
          alarm_output(ALARM_DOWN_OUTPUT);
          if (STATE_PROCCES == RESET_BUTTON) goto reset_bro;
          vTaskDelay(100); 
        }
        break;
      case RESET_BUTTON:
reset_bro:
          vTaskDelay(1500); 
        // siklus selesai
        STATE_PROCCES = STEADY;
        STATUS_CYLINDER = 0;  //
        break;
      default:
        STATE_PROCCES = 0;
    }
    lhd_burned_watch();
    vTaskDelay(250);
  }
}

bool wait_release_proccess(char number) {
  waktu_warning = 0;
  while (waktu_warning < config.delay_release * 10) {
    waktu_warning++;
    heartbeat_logic = true;  // IWDG: release sequence aktif
    alarm_output(number);
if (STATE_PROCCES == RESET_BUTTON) return 1;
    vTaskDelay(100);  // one tick delay (15ms) in between reads for stability
  }
  return 0;
}
bool wait_warning_proccess(char number) {
  waktu_warning = 0;
  while (waktu_warning < config.delay_warning * 10) {
    waktu_warning++;
    heartbeat_logic = true;  // IWDG: warning sequence aktif
    alarm_output(number);
if (STATE_PROCCES == RESET_BUTTON) return 1;
    vTaskDelay(100);  // one tick delay (15ms) in between reads for stability
  }
  return 0;
}