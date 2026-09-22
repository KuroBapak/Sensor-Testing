#define SENSOR_PIN PA0 //Pin

void setup() {
  Serial.begin(115200); 
  analogReadResolution(12); 
  pinMode(SENSOR_PIN, INPUT_ANALOG); 
  delay(1000);
}

void loop() {
  // 1. nilai raw ADC
  int rawADC = analogRead(SENSOR_PIN);
  
  // 2. Konversi ADC (0 - 4095) menjadi Tegangan dengan patokan 3.3V
  float voltage = (rawADC / 4095.0) * 3.3;
  
  // 3. Konversi Tegangan menjadi Arus (mA)
  float current_mA = (voltage / 120.0) * 1000.0;
  
  Serial.print("Raw_ADC:");
  Serial.print(rawADC);
  Serial.print("  Arus_mA:");
  Serial.println(current_mA, 3);
  delay(100);
}