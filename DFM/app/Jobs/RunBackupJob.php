<?php

namespace App\Jobs;

use App\Models\BackupRun;
use App\Models\BackupSetting;
use App\Services\BackupStorageService;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;

class RunBackupJob implements ShouldQueue
{
    use Queueable;

    public function __construct(public BackupRun $backupRun) {}

    public function handle(BackupStorageService $backupService): void
    {
        $this->backupRun->update([
            'status' => 'running',
        ]);

        $settings = BackupSetting::where('site_id', $this->backupRun->site_id)->first();

        if (! $settings) {
            $this->backupRun->update([
                'status' => 'failed',
                'finished_at' => now(),
                'error_message' => 'Backup settings not configured for this site.',
            ]);

            return;
        }

        $result = $backupService->performBackup($settings, $this->backupRun->trigger);

        if ($result['success']) {
            $this->backupRun->update([
                'status' => 'success',
                'finished_at' => now(),
                'size_bytes' => $result['details']['size_bytes'] ?? null,
                'object_key' => $result['details']['object_key'] ?? null,
                'error_message' => null,
            ]);

            if ($this->backupRun->trigger === 'test') {
                $settings->update([
                    'last_test_backup_at' => now(),
                ]);
            }
        } else {
            $this->backupRun->update([
                'status' => 'failed',
                'finished_at' => now(),
                'error_message' => $result['message'],
            ]);
        }
    }
}
