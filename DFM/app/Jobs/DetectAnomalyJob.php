<?php

namespace App\Jobs;

use App\Events\AnomalyDetected;
use App\Models\AnomalyLog;
use App\Models\Site;
use App\Models\TankLevelReading;
use App\Models\Transaction;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;

class DetectAnomalyJob implements ShouldQueue
{
    use Queueable;

    public function __construct(
        public Transaction $transaction
    ) {}

    /**
     * Run C1 Sudden-Change and C2 Transfer-Reconciliation checks.
     */
    public function handle(): void
    {
        $this->checkSuddenChange();
        $this->checkTransferReconciliation();
    }

    /**
     * C1: Compare tank level before and after the transaction window.
     * If the observed change exceeds the sensitivity threshold, flag it.
     */
    protected function checkSuddenChange(): void
    {
        $txn = $this->transaction;
        $tankId = $txn->tank_id;

        // Get the two closest readings flanking the transaction
        $readingBefore = TankLevelReading::where('tank_id', $tankId)
            ->where('timestamp', '<=', $txn->started_at)
            ->orderByDesc('timestamp')
            ->first();

        $readingAfter = TankLevelReading::where('tank_id', $tankId)
            ->where('timestamp', '>=', $txn->ended_at)
            ->orderBy('timestamp')
            ->first();

        if (! $readingBefore || ! $readingAfter) {
            return; // Not enough sensor data to compare
        }

        $observedDiff = abs($readingBefore->level_liters - $readingAfter->level_liters);
        $expectedDiff = abs($txn->liters);

        // Load sensitivity from site settings
        $site = Site::first();
        $settings = $site?->settings ?? [];
        $sensitivityMode = $settings['sensitivity_mode'] ?? 'medium';
        $thresholdPercent = match ($sensitivityMode) {
            'high' => 5.0,
            'low' => 20.0,
            default => 10.0,
        };

        // If expected diff is zero (shouldn't happen), skip
        if ($expectedDiff <= 0) {
            return;
        }

        $deviationPercent = abs($observedDiff - $expectedDiff) / $expectedDiff * 100;

        if ($deviationPercent > $thresholdPercent) {
            $anomaly = AnomalyLog::create([
                'anomaly_type' => 'sudden_change_theft',
                'tank_id' => $tankId,
                'device_id' => $txn->device_id,
                'tag_id' => $txn->tag_id,
                'transaction_id' => $txn->id,
                'volume_diff' => $observedDiff - $expectedDiff,
                'observed_percent' => $deviationPercent,
                'threshold_percent' => $thresholdPercent,
                'sensitivity_mode' => $sensitivityMode,
                'anomaly_time' => $txn->ended_at,
                'latitude' => $readingAfter->latitude,
                'longitude' => $readingAfter->longitude,
                'position_time' => $readingAfter->timestamp,
                'status_investigasi' => 'open',
                'meta' => [
                    'level_before' => $readingBefore->level_liters,
                    'level_after' => $readingAfter->level_liters,
                    'expected_diff' => $expectedDiff,
                    'observed_diff' => $observedDiff,
                ],
            ]);

            broadcast(new AnomalyDetected($anomaly));
        }
    }

    /**
     * C2: Cross-check dispense transaction against main tank records.
     * If browser received fuel but main tank shows no matching outflow, flag it.
     */
    protected function checkTransferReconciliation(): void
    {
        $txn = $this->transaction;

        // Only applies to dispense_to_browser transactions with a main_tank_id
        if ($txn->transfer_type !== 'dispense_to_browser' || ! $txn->main_tank_id) {
            return;
        }

        // Look for a matching main tank outflow within ±10 minutes
        $matchingOutflow = Transaction::where('tank_id', $txn->main_tank_id)
            ->where('transfer_type', 'fill_to_main')
            ->whereBetween('started_at', [
                $txn->started_at->subMinutes(10),
                $txn->ended_at->addMinutes(10),
            ])
            ->exists();

        if (! $matchingOutflow) {
            // Find latest coordinates for this tank/device
            $latestReading = TankLevelReading::where('tank_id', $txn->tank_id)
                ->whereNotNull('latitude')
                ->whereNotNull('longitude')
                ->orderByDesc('timestamp')
                ->first();

            $anomaly = AnomalyLog::create([
                'anomaly_type' => 'calibration_needed',
                'tank_id' => $txn->tank_id,
                'device_id' => $txn->device_id,
                'transaction_id' => $txn->id,
                'volume_diff' => $txn->liters,
                'anomaly_time' => $txn->ended_at,
                'latitude' => $latestReading?->latitude,
                'longitude' => $latestReading?->longitude,
                'position_time' => $latestReading?->timestamp,
                'status_investigasi' => 'open',
                'meta' => [
                    'check' => 'transfer_reconciliation',
                    'browser_tank_id' => $txn->tank_id,
                    'main_tank_id' => $txn->main_tank_id,
                    'liters' => $txn->liters,
                ],
            ]);

            broadcast(new AnomalyDetected($anomaly));
        }
    }
}
