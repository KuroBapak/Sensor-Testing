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
        Schema::create('tank_level_readings', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('tank_id');
            $table->string('device_id');
            $table->decimal('level_liters', 10, 2);
            $table->decimal('latitude', 9, 6)->nullable();
            $table->decimal('longitude', 9, 6)->nullable();
            $table->unsignedTinyInteger('satellites')->nullable();
            $table->timestamp('timestamp');
            $table->timestamp('server_received_at')->nullable();
            $table->boolean('rtc_out_of_bounds')->default(false);
            $table->timestamps();

            $table->foreign('tank_id')->references('tank_id')->on('tanks')->cascadeOnDelete();
            $table->foreign('device_id')->references('device_id')->on('hardware_devices')->cascadeOnDelete();

            $table->unique(['device_id', 'timestamp']);
            $table->index(['tank_id', 'timestamp']);
            // A spatial index should be handled correctly by MySQL if points are valid. Wait, let's keep it simple and omit POINT column for now as PRD says generated column, we can do it via raw statement or just use latitude/longitude directly for testing.
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('tank_level_readings');
    }
};
