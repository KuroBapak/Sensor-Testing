// Pengawas IWDG. Task kecil tanpa printf/serial: hanya membaca flag tanda-hidup dan memberi makan IWDG.
// IWDG sendiri sudah dimulai paling awal di setup(). Hasil pengecekan dilaporkan lewat wdg_fail/wdg_alive
// dan dicetak oleh TaskDebug, supaya pemberi makan tidak pernah bergantung pada serial yang bisa tersendat.
void TaskWdg(void *pvParameters __attribute__((unused)))  // This is a Task.
{
  // Masa boot: beri makan tanpa syarat sampai keempat task kritis menandai hidup sekali.
  // Boot bisa lambat (lcd.begin() ~1 detik x2 berjalan busy-wait di prioritas lebih tinggi dan menahan
  // TaskInput/TaskAnalog/TaskControl). Batas atas WDG_BOOT_GRACE_MS: bila ada task yang memang tidak pernah
  // hidup, pengecekan di bawah gagal, IWDG tidak diberi makan, dan MCU reset.
  for (uint16_t t = 0; t < WDG_BOOT_GRACE_MS / 100; t++) {
    if (heartbeat_analog && heartbeat_logic && heartbeat_control && heartbeat_input) break;
    IWatchdog.reload();
    vTaskDelay(pdMS_TO_TICKS(100));
  }

  for (;;)  // A Task shall never return or exit.
  {
    // Feed watchdog HANYA jika task kritis masih hidup.
    // Saat urutan alarm (WARNING/RELEASE) sedang berjalan, hanya TaskLogic yang wajib hidup: reset di
    // tengah urutan membuang state dan waktunya. Di luar itu semua task kritis wajib hidup.
    bool alarm_berjalan = (STATE_PROCCES >= WARNING_LHD && STATE_PROCCES <= RELEASE_CALLBUTTON);
    if (heartbeat_logic && (alarm_berjalan || (heartbeat_analog && heartbeat_control && heartbeat_input))) {
      wdg_fail = false;
      IWatchdog.reload();
    } else {
      // Minimal 1 task kritis tidak merespons: JANGAN feed watchdog, MCU reset paling lama WDG_TIMEOUT_US.
      wdg_alive = (heartbeat_analog ? 1 : 0) | (heartbeat_logic ? 2 : 0) | (heartbeat_control ? 4 : 0) |
                  (heartbeat_input ? 8 : 0);
      wdg_fail = true;
    }
    // SELALU clear flag setiap siklus: mencegah flag lama dari siklus sebelumnya menutupi task yang hang.
    heartbeat_analog  = false;
    heartbeat_logic   = false;
    heartbeat_control = false;
    heartbeat_input   = false;

    vTaskDelay(pdMS_TO_TICKS(WDG_WINDOW_MS));
  }
}
