#include <Wire.h>
#include <Adafruit_ADS1X15.h>

Adafruit_ADS1115 ads; 

void setup() {
  Serial.begin(115200); 
  
  // Set gain ke 1x (rentang maksimal +/- 4.096V)
  ads.setGain(GAIN_ONE); 
  
  if (!ads.begin()) {
    Serial.println("Gagal menemukan ADS1115. Cek kabel SDA/SCL!");
    while (1); 
  }
  
  delay(1000);
}

void loop() {
  // 1. Baca nilai raw ADC
  int16_t rawADC = ads.readADC_SingleEnded(0);
  float voltage = ads.computeVolts(rawADC);
  float current_mA = (voltage / 120.0) * 1000.0;
  
  Serial.print("Raw_ADC:");
  Serial.print(rawADC);
  Serial.print("  Arus_mA:");
  Serial.println(current_mA, 3);
  
  delay(100);
}