/*
 * @file    hw_stabil.h
 * @brief   Jam HAL yang tidak bergantung pada SysTick.
 *
 *  1. Vector table disalin ke RAM, supaya handler TIM4 bisa dipasang tanpa bentrok dengan core.
 *  2. Jam HAL (HAL_GetTick) digerakkan TIM4 (1 kHz), bukan SysTick. Semua loop tunggu bertimeout di driver
 *     (I2C, UART, flash) tetap berakhir walau SysTick berhenti.
 *  3. Clock PWR + BKP dan akses backup domain dinyalakan, sama seperti pada build yang diuji stabil.
 *
 * Penangkap freeze (reset langsung saat HardFault/NMI, Clock Security System, pengawas TaskLogic berbasis
 * TIM4) sudah dihapus. Perlindungan freeze tinggal IWDG (dimulai di setup(), diberi makan oleh TaskWdg).
 */

static uint32_t hw_vtor[64] __attribute__((aligned(256)));  // F103: 16 + 43 = 59 entri -> 64 kata
static volatile uint32_t hw_ms = 0;                         // jam HAL pengganti (1 ms per interupsi TIM4)
static volatile bool hw_tick_on = false;

// Dipakai TaskLogic: tanda "masih hidup" untuk supervisor IWDG.
#define HB_LOGIC()          \
  do {                      \
    heartbeat_logic = true; \
  } while (0)

// Menggantikan HAL_GetTick() bawaan HAL (weak). Sebelum diaktifkan memakai uwTick (SysTick) seperti biasa,
// supaya inisialisasi clock di awal boot tidak menunggu jam yang belum jalan.
extern "C" uint32_t HAL_GetTick(void) {
  return hw_tick_on ? hw_ms : uwTick;
}

extern "C" void hw_tim4_isr(void) {
  TIM4->SR = 0;
  hw_ms = hw_ms + 1;
}

// Panggil sekali di awal setup(), sebelum scheduler berjalan.
static void hw_stabil_init(void) {
  RCC->APB1ENR |= RCC_APB1ENR_PWREN | RCC_APB1ENR_BKPEN;
  PWR->CR |= PWR_CR_DBP;

  const uint32_t *src = (const uint32_t *)SCB->VTOR;
  for (int i = 0; i < 64; i++) hw_vtor[i] = src[i];
  hw_vtor[16 + TIM4_IRQn] = ((uint32_t)&hw_tim4_isr) | 1u;  // jam HAL
  __DSB();
  SCB->VTOR = (uint32_t)hw_vtor;
  __DSB();
  __ISB();

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
}
