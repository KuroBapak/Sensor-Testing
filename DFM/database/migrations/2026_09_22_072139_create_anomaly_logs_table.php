<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('anomaly_logs', function (Blueprint $table) {
            $table->id();
            $table->enum('anomaly_type', ['sudden_change_theft', 'calibration_needed', 'unauthorized_scan', 'geofence_exit']);
            $table->unsignedBigInteger('tank_id')->nullable();
            $table->string('device_id')->nullable();
            $table->string('tag_id')->nullable();
            $table->unsignedBigInteger('transaction_id')->nullable();
            $table->unsignedBigInteger('geofence_id')->nullable();
            $table->uuid('device_event_id')->nullable();
            $table->decimal('volume_diff', 10, 2)->nullable();
            $table->decimal('observed_percent', 5, 2)->nullable();
            $table->decimal('threshold_percent', 5, 2)->nullable();
            $table->enum('sensitivity_mode', ['low', 'medium', 'high'])->nullable();
            $table->timestamp('anomaly_time');
            $table->decimal('latitude', 9, 6)->nullable();
            $table->decimal('longitude', 9, 6)->nullable();
            $table->timestamp('position_time')->nullable();
            $table->enum('status_investigasi', ['open', 'investigating', 'resolved', 'false_positive'])->default('open');
            $table->text('resolution_note')->nullable();
            $table->foreignId('resolved_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('resolved_at')->nullable();
            $table->json('meta')->nullable();
            $table->timestamps();

            $table->foreign('tank_id')->references('tank_id')->on('tanks')->nullOnDelete();
            $table->foreign('device_id')->references('device_id')->on('hardware_devices')->nullOnDelete();
            $table->foreign('tag_id')->references('tag_id')->on('rfid_tags')->nullOnDelete();
            $table->foreign('transaction_id')->references('id')->on('transactions')->nullOnDelete();
            $table->foreign('geofence_id')->references('id')->on('geofences')->nullOnDelete();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('anomaly_logs');
    }
};
