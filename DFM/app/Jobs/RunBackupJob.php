<?php

namespace App\Jobs;

use App\Models\BackupRun;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;

class RunBackupJob implements ShouldQueue
{
    use Queueable;

    public function __construct(public BackupRun $backupRun) {}

    public function handle(): void
    {
        // Mark as running
        $this->backupRun->update([
            'status' => 'running',
        ]);

        try {
            // Perform backup logic here (e.g. database dump, upload to S3)
            // Simulated backup process:
            sleep(2);

            // Mark as success
            $this->backupRun->update([
                'status' => 'success',
                'finished_at' => now(),
                'size_bytes' => rand(1024 * 1024, 1024 * 1024 * 50),
                'object_key' => 'backups/manual/backup-'.now()->timestamp.'.sql.gz',
            ]);
        } catch (\Exception $e) {
            $this->backupRun->update([
                'status' => 'failed',
                'finished_at' => now(),
                'error_message' => $e->getMessage(),
            ]);
        }
    }
}
