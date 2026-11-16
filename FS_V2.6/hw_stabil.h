/*
 * @file    hw_stabil.h
 * @brief   Pelindung agar kegagalan apa pun hanya berakibat reset singkat, bukan freeze. Tanpa logging,
 *          tanpa blocking. Tidak bergantung pada TaskDebug maupun LCD.
 *
 *  1. Vector table disalin ke RAM, supaya pengambilan alamat interrupt tidak menunggu flash.
 *  2. HardFault -> reset langsung (sebelumnya library membekukan CPU sampai IWDG mereset, ~8 s).
 *  3. Kristal HSE mati (Clock Security System) -> NMI -> reset langsung.
 *  4. Pengawas TaskLogic: timer TIM4 (1 kHz, prioritas interrupt tertinggi) mereset MCU bila TaskLogic
 *     (yang menjalankan urutan alarm dan menggerakkan detonator) tidak menandai "hidup" selama
 *     HW_STALL_RESET_MS. Yang diawasi TaskLogic, bukan TaskDebug/LCD.
 *  5. Jam HAL (HAL_GetTick) digerakkan TIM4, bukan SysTick. Semua loop tunggu bertimeout di driver
 *     (I2C, UART, flash) tetap berakhir walau SysTick berhenti. Pada freeze yang tertangkap, driver I2C
 *     berputar selamanya karena jam HAL berhenti.
 *  6. Clock PWR + BKP dan akses backup domain dinyalakan, sama seperti pada build yang diuji stabil.
 *
 * Set HW_STABIL ke 0 untuk mematikan semuanya (perilaku sebelum paket ini).
 */

#define HW_STABIL 1
#define HW_STALL_RESET_MS 4000  // jeda tanda-hidup terpanjang yang wajar di TaskLogic sekitar 2 detik

#if HW_STABIL
static uint32_t hw_vtor[64] __attribute__((aligned(256)));  // F103: 16 + 43 = 59 entri -> 64 kata
static volatile uint16_t hw_hb_logic = 0;  // naik setiap TaskLogic menandai hidup
static uint16_t hw_last = 0;
static uint16_t hw_stall_ms = 0;
static bool hw_armed = false;              // aktif setelah tanda hidup pertama TaskLogic
static volatile uint32_t hw_ms = 0;        // jam HAL pengganti (1 ms per interupsi TIM4)
static volatile bool hw_tick_on = false;

// Dipakai TaskLogic di semua tempat yang tadinya hanya "heartbeat_logic = true".
#define HB_LOGIC()                                  \
  do {                                              \
    heartbeat_logic = true;                         \
    hw_hb_logic = (uint16_t)(hw_hb_logic + 1);      \
  } while (0)

// Menggantikan HAL_GetTick() bawaan HAL (weak). Sebelum diaktifkan memakai uwTick (SysTick) seperti biasa,
// supaya inisialisasi clock di awal boot tidak menunggu jam yang belum jalan.
extern "C" uint32_t HAL_GetTick(void) {
  return hw_tick_on ? hw_ms : uwTick;
}

extern "C" void hw_reset_isr(void) {  // HardFault dan NMI (CSS)
  NVIC_SystemReset();
}

extern "C" void hw_tim4_isr(void) {
  TIM4->SR = 0;
  hw_ms = hw_ms + 1;
  uint16_t p = hw_hb_logic;
  if (!hw_armed) {
    if (p != 0) {
      hw_armed = true;
      hw_last = p;
      hw_stall_ms = 0;
    }
    return;
  }
  if (p != hw_last) {
    hw_last = p;
    hw_stall_ms = 0;
    return;
  }
  hw_stall_ms = (uint16_t)(hw_stall_ms + 1);
  if (hw_stall_ms >= HW_STALL_RESET_MS) NVIC_SystemReset();
}
#else
#define HB_LOGIC()          \
  do {                      \
    heartbeat_logic = true; \
  } while (0)
#endif

// Panggil sekali di awal setup(), sebelum scheduler berjalan.
static void hw_stabil_init(void) {
#if HW_STABIL
  RCC->APB1ENR |= RCC_APB1ENR_PWREN | RCC_APB1ENR_BKPEN;
  PWR->CR |= PWR_CR_DBP;

  const uint32_t *src = (const uint32_t *)SCB->VTOR;
  for (int i = 0; i < 64; i++) hw_vtor[i] = src[i];
  hw_vtor[2] = ((uint32_t)&hw_reset_isr) | 1u;              // NMI (CSS)
  hw_vtor[3] = ((uint32_t)&hw_reset_isr) | 1u;              // HardFault
  hw_vtor[16 + TIM4_IRQn] = ((uint32_t)&hw_tim4_isr) | 1u;  // pengawas + jam HAL
  __DSB();
  SCB->VTOR = (uint32_t)hw_vtor;
  __DSB();
  __ISB();

  if (RCC->CR & RCC_CR_HSEON) RCC->CR |= RCC_CR_CSSON;  // kristal mati -> NMI -> reset

  RCC->APB1ENR |= RCC_APB1ENR_TIM4EN;
  __DSB();
  uint32_t pclk1 = HAL_RCC_GetPCLK1Freq();
  uint32_t tclk = (RCC->CFGR & RCC_CFGR_PPRE1_2) ? pclk1 * 2 : pclk1;  // clock timer APB1
  TIM4->PSC = (uint16_t)(tclk / 1000000UL - 1);                         // 1 MHz
  TIM4->ARR = 999;                                                      // 1 kHz
  TIM4->EGR = TIM_EGR_UG;
  TIM4->SR = 0;
  TIM4->DIER = TIM_DIER_UIE;
  NVIC_SetPriority(TIM4_IRQn, 0);  // lebih tinggi dari semua ISR lain dan dari batas FreeRTOS
  NVIC_EnableIRQ(TIM4_IRQn);
  hw_ms = uwTick;                  // lanjutkan jam HAL dari nilai SysTick saat ini
  hw_tick_on = true;
  TIM4->CR1 = TIM_CR1_CEN;
#endif
}
