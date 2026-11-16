/*
 * @file    i2c_lock.h
 * @brief   Kunci (mutex) untuk bus I2C.
 *
 * Bus I2C dipakai dua task: TaskAnalog (RTC DS3231 + ADC ADS1115) dan TaskDisplay (LCD). Library Wire
 * tidak aman dipakai dua task sekaligus: bila satu task menyela transaksi task lain, status peripheral
 * bisa menggantung "sibuk" dan loop tunggu di driver berputar sampai timeout.
 *
 * Mutex rekursif dengan pewarisan prioritas FreeRTOS. Pemegang kunci naik ke prioritas penunggu
 * tertinggi, jadi tidak ada inversi prioritas. Semua penunggu punya batas waktu (tidak menunggu
 * selamanya): bila TaskDisplay kehabisan waktu, operasi LCD dilewati; bila TaskAnalog kehabisan waktu,
 * ia tetap membaca sensor (deteksi tidak boleh berhenti).
 */

static SemaphoreHandle_t i2c_mtx = NULL;

#define I2C_LOCK_MS_LCD    500   // maksimal menunggu kunci untuk satu operasi LCD
#define I2C_LOCK_MS_SENSOR 300   // maksimal menunggu kunci untuk satu pembacaan sensor
#define I2C_LOCK_MS_BOOT   3000  // saat inisialisasi

// Panggil sekali di setup(), sebelum task dibuat.
static void i2c_lock_init(void) {
  if (i2c_mtx == NULL) i2c_mtx = xSemaphoreCreateRecursiveMutex();
}

// true bila kunci didapat. Jika false, JANGAN panggil i2c_unlock().
static bool i2c_lock(uint32_t timeout_ms) {
  if (i2c_mtx == NULL) return false;
  return xSemaphoreTakeRecursive(i2c_mtx, pdMS_TO_TICKS(timeout_ms)) == pdTRUE;
}

static void i2c_unlock(void) {
  xSemaphoreGiveRecursive(i2c_mtx);
}

// LCD yang setiap operasinya memegang kunci I2C. Hanya TaskDisplay yang memakai objek ini.
class LockedLCD : public LiquidCrystal_I2C {
 public:
  LockedLCD(uint8_t addr, uint8_t cols, uint8_t rows) : LiquidCrystal_I2C(addr, cols, rows) {}

  void begin() {
    bool got = i2c_lock(I2C_LOCK_MS_BOOT);
    LiquidCrystal_I2C::begin();  // inisialisasi harus tetap berjalan walau kunci tidak didapat
    if (got) i2c_unlock();
  }

  void backlight() {
    bool got = i2c_lock(I2C_LOCK_MS_LCD);
    LiquidCrystal_I2C::backlight();
    if (got) i2c_unlock();
  }

  void setCursor(uint8_t col, uint8_t row) {
    if (!i2c_lock(I2C_LOCK_MS_LCD)) return;  // lewati operasi LCD, jangan menabrak transaksi lain
    LiquidCrystal_I2C::setCursor(col, row);
    i2c_unlock();
  }

  virtual size_t write(uint8_t value) {
    if (!i2c_lock(I2C_LOCK_MS_LCD)) return 0;
    size_t n = LiquidCrystal_I2C::write(value);
    i2c_unlock();
    return n;
  }

  using Print::write;  // tetap sediakan write(const char*) dan write(buffer, size)
};
