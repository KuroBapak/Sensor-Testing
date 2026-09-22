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
        Schema::create('transactions', function (Blueprint $table) {
            $table->id();
            $table->uuid('device_txn_id')->nullable();
            $table->string('device_id')->nullable();
            $table->enum('transfer_type', ['fill_to_main', 'dispense_to_browser', 'vendor_fill']);
            $table->string('tag_id')->nullable();
            $table->unsignedBigInteger('tank_id')->nullable();
            $table->unsignedBigInteger('main_tank_id')->nullable();
            $table->decimal('liters', 10, 2);
            $table->timestamp('started_at');
            $table->timestamp('ended_at');
            $table->timestamp('server_received_at')->nullable();
            $table->boolean('rtc_out_of_bounds')->default(false);
            $table->enum('sync_status', ['live', 'backfilled'])->default('live');
            $table->foreignId('entered_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();

            $table->foreign('device_id')->references('device_id')->on('hardware_devices')->nullOnDelete();
            $table->foreign('tag_id')->references('tag_id')->on('rfid_tags')->nullOnDelete();
            $table->foreign('tank_id')->references('tank_id')->on('tanks')->nullOnDelete();
            $table->foreign('main_tank_id')->references('tank_id')->on('tanks')->nullOnDelete();

            $table->unique(['device_id', 'device_txn_id']);
            $table->index(['tank_id', 'started_at']);
            $table->index(['main_tank_id', 'ended_at']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('transactions');
    }
};
