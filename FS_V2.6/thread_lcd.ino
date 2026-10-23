void TaskDisplay(void *pvParameters __attribute__((unused)))  // This is a Task.
{
  setup_display();
  setup_lcd = 1;
  for (;;) {
    while (wait_asecond) vTaskDelay(100);
    xSemaphoreTake(sem, portMAX_DELAY);
    if (!menu_state) {
      lcd.setCursor(0, 0);
      lcd.printf("   PANEL BTR %s   ", NO_UNIT[PANEL_UNIT]);
      lcd.setCursor(0, 3);
      lcd.print(buf);
      if (STATE_PROCCES == STEADY) {
      lcd.setCursor(0, 1); 
      lcd.printf("%s", CYCLE_STATUS[STATE_PROCCES]);
        if (!STATUS_PRESSURE && !LHD) {
          lcd.setCursor(0, 2);    
          lcd.printf("%s", LHD_STATUS[LHD]);
        } else if (STATUS_PRESSURE || LHD) {
          lcd.setCursor(0, 2);
          lcd.printf("%s", PRESSURE_STATUS[STATUS_PRESSURE]);
          if (wait_delay(1000))  goto otw_logger;
          lcd_kosong(2);
          if (wait_delay(400))  goto otw_logger;
          lcd.setCursor(0, 2);
          lcd.printf("%s", LHD_STATUS[LHD]);
          if (wait_delay(900))  goto otw_logger;
          lcd_kosong(2);
          if (wait_delay(400))  goto otw_logger;
        }
      }
      else if (STATE_PROCCES == ALARM_OFF) 
      {
      lcd.setCursor(0, 1);
      lcd.printf(" 1.LHD : %d.%02d / %d.%02d", (int)voltage, (int)(voltage * 100) % 100, (int)VOLTAGE_SHORT_MIN, (int)(VOLTAGE_SHORT_MIN * 100) % 100);
      lcd.setCursor(0, 2);
      lcd.printf(" 2.CB  : %s", CALLBUTTON ? "OPEN " : "CLOSE ");
      }
      if (STATE_PROCCES > STEADY) {
      lcd.setCursor(0, 1); 
      lcd.printf("%s", CYCLE_STATUS[STATE_PROCCES]);
        if (STATE_PROCCES == WARNING_LHD) {
          char display_timer[50];
          sprintf(display_timer, "  TIMER:  %d         ", (config.delay_warning - (waktu_warning / 10)));
          lcd.setCursor(0, 2);
          lcd.printf("%s", display_timer);
        } else {
          lcd.setCursor(0, 2);
          lcd.printf("%s", CYLINDER_STATUS[STATUS_CYLINDER]);
        }
        if (STATUS_CYLINDER == 5) {
          if (wait_delay(1000))  goto otw_logger;
        lcd_kosong(2);
          if (wait_delay(900))  goto otw_logger;
        } else if (STATUS_CYLINDER == 1 || STATUS_CYLINDER == 2 || STATUS_CYLINDER == 3 || STATUS_CYLINDER == 4) {
          if (wait_delay(500))  goto otw_logger;
        lcd_kosong(2);
          if (wait_delay(500))  goto otw_logger;
        }
        lcd.setCursor(0, 3);
        lcd.print(buf);
      }
    } 
    else if (menu_state == LCD_MENU_SETTING)     menu_setting(cursor_state);
    else if (menu_state == LCD_MENU_1_UNIT_INFO)
     {
           lcd.setCursor(0, 0);
      lcd.print("INFO UNIT           ");
      lcd.setCursor(0, 1);
      lcd.printf("Unit Number:%s   ", NO_UNIT[PANEL_UNIT]);
      lcd.setCursor(0, 3);
      lcd.print("HW. Version:V2.2.0 ");
      lcd.setCursor(0, 2);
      lcd.print("Prod. Date :19/05/25");
    }
    else if (menu_state >= LCD_MENU_2_SETTING_PARAMETER && menu_state < 10) {
        lcd.setCursor(0, 0);
        lcd.print("SETTING PARAMETER ");
      if (!cursor_state) {
        lcd_kosong(3);
        lcd.setCursor(0, 2);
        lcd.printf(" 2.D.Release:  [%02ds]", (int)config.delay_release);
        lcd.setCursor(0, 1);
        lcd.printf(">1.D.Warning:  [%02ds]", (int)config.delay_warning);
        if (edit_state) {
         vTaskDelay(750);  // one tick delay (15ms) in between reads for stability
          lcd.setCursor(0, 1);
          lcd.print(">1.D.Warning:       ");  // MANUAL
         vTaskDelay(200);  // one tick delay (15ms) in between reads for stability
        }
      }
      if (cursor_state == 1) 
      {
        lcd_kosong(3); 
        lcd.setCursor(0, 1);
        lcd.printf(" 1.D.Warning:  [%02ds]", (int)config.delay_warning);
        lcd.setCursor(0, 2);
        lcd.printf(">2.D.Release:  [%02ds]", (int)config.delay_release);
        if (edit_state) {
         vTaskDelay(750);  // one tick delay (15ms) in between reads for stability
          lcd.setCursor(0, 2);
          lcd.print(">2.D.Release:       ");  // MANUAL
         vTaskDelay(200);  // one tick delay (15ms) in between reads for stability
        }
      }
    } else if (menu_state == LCD_ALARM_LOGGING) {
      otw_logger:
      page_logger(cursor_alarm_state);
  }
  vTaskDelay(50);  // one tick delay (15ms) in between reads for stability
}
}

void page_logger(char pages)
{
char kursor;
char counting_alarm_;
counting_alarm_ = config.alarm_count;
if ( counting_alarm_ > 9 ) counting_alarm_ = 9;
      if (pages == 0) kursor = pages;          // 0, 1, 2
      else if (pages == 1 || pages == 2) kursor = pages *3;  // 3, 4, 5
      lcd.setCursor(0, 0);
      lcd.printf("LOG ALARM(%d)     %d/3", counting_alarm_,pages+1);
      if (config.alarm_count > kursor)
      {
      lcd.setCursor(0, 1);
      lcd.printf("%s-%s", date_log[kursor], ALARM_STATE[state_log[kursor]]);
      }
      else lcd_kosong(1);
      if (config.alarm_count > kursor + 1)
      {
      kursor++;
      lcd.setCursor(0, 2);
      lcd.printf("%s-%s", date_log[kursor], ALARM_STATE[state_log[kursor]]);
      }
      else lcd_kosong(2);
      if (config.alarm_count > kursor + 1)
      {
      kursor++;
      lcd.setCursor(0, 3);
      lcd.printf("%s-%s", date_log[kursor], ALARM_STATE[state_log[kursor]]);
      }
      else lcd_kosong(3);
}


bool wait_delay(int waktu)
{
  // misal 1000
  // 1000 / 10 = 100
  int waktu_s = 0;
  while (waktu_s < waktu / 100) 
  {
    waktu_s++;
   if (menu_state == LCD_ALARM_LOGGING) return 1;
    vTaskDelay(100);  // one tick delay (15ms) in between reads for stability
  }
  return 0;
}


void setup_display(void){
    char loading = 1;
lcd.begin();  // beginialize the lcd
  lcd.begin();
  lcd.backlight();
  lcd.setCursor(0, 0);
  lcd.print(NAMA_PANEL);
  lcd.setCursor(0, 2);
  lcd.print(NAMA_MAKER);
  lcd.setCursor(0, 3);
  lcd.print(VERSI_PROGRAM);

  while (loading < 19) {
    lcd.setCursor(loading, 1);
    lcd.print(">");
    if (loading < 17) vTaskDelay(200);
    else vTaskDelay(100);
    loading++;
  }
  vTaskDelay(10);
  while (loading > 0) {
    lcd.setCursor(loading, 1);
    lcd.print(" ");
    vTaskDelay(100);
    loading--;
  }
  vTaskDelay(500);  // one tick delay (15ms) in between reads for stability
}


void menu_setting(char kursor)
{
        lcd.setCursor(0, 0);
        lcd.print("MENU SETTING        ");
      if (!kursor) {
        lcd.setCursor(0, 1); 
        lcd.print(">1.Info Panel       ");
        lcd.setCursor(0, 2);
        lcd.print(" 2.Setting Parameter");
        lcd.setCursor(0, 3);
        lcd.print("                    ");
      }
      if (kursor == 1) {
        lcd.setCursor(0, 1);
        lcd.print(" 1.Info Panel       ");
        lcd.setCursor(0, 2);
        lcd.print(">2.Setting Parameter");
        lcd.setCursor(0, 3);
        lcd.print("                    ");
      }
}

void lcd_kosong(char kursor)
{
      lcd.setCursor(0, kursor);
      lcd.printf("                    ");
}