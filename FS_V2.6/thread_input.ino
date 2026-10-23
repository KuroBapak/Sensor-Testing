void TaskInput(void *pvParameters __attribute__((unused)))  // This is a Task.
{
  for (;;)  // A Task shall never return or exit.
  {
    heartbeat_input = true;  // IWDG: pembacaan tombol+sensor masih berjalan
    while (wait_asecond) vTaskDelay(100);
    check_pressure();
    vTaskDelay(button_input() ? 500 : 150);
  }
}

char check_pressure(void) {
  PRESSURE_1 = DIN_PRESSURE_1.state();
  PRESSURE_2 = DIN_PRESSURE_2.state();
  if (!PRESSURE_1 && !PRESSURE_2) STATUS_PRESSURE = 0;
  else if (PRESSURE_1 && !PRESSURE_2) STATUS_PRESSURE = 1;
  else if (!PRESSURE_1 && PRESSURE_2) STATUS_PRESSURE = 2;
  else if (PRESSURE_1 && PRESSURE_2) STATUS_PRESSURE = 3;
  return STATUS_PRESSURE;
}

bool button_input(void) {
  CALLBUTTON = DIN_CALLBUTTON.state();
  if (!DOWN.state() && !UP.state() && RESET_BTN.state() && INFO.state()) {
    if (STATE_PROCCES == STEADY) STATE_PROCCES = ALARM_OFF;
    else if (STATE_PROCCES == ALARM_OFF) STATE_PROCCES = STEADY;
    vTaskDelay(100);  // one tick delay (15ms) in between reads for stability
    return 1;
  }
  if (DOWN.wasPressed() && UP.state() && RESET_BTN.state() && INFO.state()) {
    if (menu_state == LCD_ALARM_LOGGING && cursor_alarm_state == 2)  cursor_alarm_state = 0; 
    else if (menu_state == LCD_ALARM_LOGGING && cursor_alarm_state < 2)   cursor_alarm_state++;
    else if (menu_state == LCD_MENU_SETTING) cursor_state = !cursor_state;
    else if (menu_state == LCD_MENU_2_SETTING_PARAMETER && edit_state == 0) cursor_state = !cursor_state;
    else if (menu_state == LCD_MENU_2_SETTING_PARAMETER && edit_state == EDIT_DELAY_WARNING && config.delay_warning > 0) config.delay_warning--;
    else if (menu_state == LCD_MENU_2_SETTING_PARAMETER && edit_state == EDIT_DELAY_RELEASE && config.delay_release > 0) config.delay_release--;
    return 1;
  }
  if (UP.wasPressed() && DOWN.state() && RESET_BTN.state() && INFO.state()) {
    if (menu_state == LCD_ALARM_LOGGING && cursor_alarm_state == 0)  cursor_alarm_state = 2; 
    else if (menu_state == LCD_ALARM_LOGGING && cursor_alarm_state > 0) cursor_alarm_state--;
    else if (menu_state == 1) cursor_state = !cursor_state;
    else if (menu_state == LCD_MENU_2_SETTING_PARAMETER && edit_state == 0) cursor_state = !cursor_state;
    else if (menu_state == LCD_MENU_2_SETTING_PARAMETER && edit_state == EDIT_DELAY_WARNING && config.delay_warning < 255) config.delay_warning++;
    else if (menu_state == LCD_MENU_2_SETTING_PARAMETER && edit_state == EDIT_DELAY_RELEASE && config.delay_release < 255) config.delay_release++;
    vTaskDelay(100);  // one tick delay (15ms) in between reads for stability
        return 1;
  }
  if (RESET_BTN.wasPressed() && INFO.wasPressed() && DOWN.state() && UP.state()) {
    if (menu_state == 0) {
      menu_state = !menu_state;
      cursor_state = 0;
      vTaskDelay(100);  // one tick delay (15ms) in between reads for stability
    }
        return 1;
  }
  if (!UP.state() && !RESET_BTN.state() && DOWN.state() && INFO.state()) {
    STATE_PROCCES = 9;
    reset_alarm_logger();
    for (int xxx = 0; xxx < 15; xxx++) {
      vTaskDelay(100);  // one tick delay (15ms) in between reads for stability
    }
    NVIC_SystemReset();
  }
  if (INFO.wasPressed() && RESET_BTN.state() && DOWN.state() && UP.state()) {  // kebalik asu info adalah reset dan menu intu info
    if (menu_state == 0 && STATE_PROCCES == STEADY) {
      read_alarm_log(0);
      menu_state = LCD_ALARM_LOGGING;
      cursor_alarm_state = 0;
      return 1;
    } else if (menu_state == LCD_ALARM_LOGGING && STATE_PROCCES == STEADY) {
      cursor_alarm_state = 0;
      menu_state = 0;
      return 1;
    } else if (menu_state == LCD_MENU_SETTING && !cursor_state) {
      menu_state = LCD_MENU_1_UNIT_INFO;
      cursor_state = 0;
      return 1;
    }
    else if (menu_state == LCD_MENU_SETTING && cursor_state == 1) {
      menu_state = LCD_MENU_2_SETTING_PARAMETER;
      cursor_state = 0;
        return 1;
    } 
    else if (menu_state == 2)  // masuk info module
    {
      menu_state = 1;
      cursor_state = 0;
      edit_state = 0;
        return 1;
    } 
    else if (menu_state == 3)  // ketika mode edit maka tidak bisa di exit
    {
      if (!edit_state) 
      { 
        edit_state = cursor_state + 1;  // ketika kursor di enter maka mode edit
                return 1;
      }
      else if (edit_state == EDIT_DELAY_WARNING) {
        edit_state = 0;
        write_delay_warning();
                return 1;
      } else if (edit_state == EDIT_DELAY_RELEASE) {
        edit_state = 0;
        write_delay_release();
                return 1;
      }
        vTaskDelay(100);  // one tick delay (15ms) in between reads for stability
    }
  }
  if (!RESET_BTN.state() && INFO.state() && DOWN.state() && UP.state()) {  // jika dimenu 1 atau lebih maka fungsi reset akan jadi sebagai tombol back
    if (menu_state == 2 && !edit_state) 
    {
      menu_state = 1;
       return 1;
    }
    else if (menu_state == 3 && !edit_state) 
    {
      menu_state = 1;
      return 1;
    }
    else if (menu_state == 1) 
    {
      menu_state = 0; 
      return 1;
    }
    else if (menu_state == 0 && STATE_PROCCES == 1)
    {
       status_state = !status_state;
       return 1;
    }
    else if (menu_state == 0 && STATE_PROCCES > 0) 
    {
      STATE_PROCCES = RESET_BUTTON;
      return 1;
    }
  }
  digitalToggle(DO_2);
  return 0;
}