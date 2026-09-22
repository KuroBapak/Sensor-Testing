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
        Schema::create('hardware_devices', function (Blueprint $table) {
            $table->string('device_id')->primary();
            $table->foreignId('site_id')->nullable()->constrained('sites')->nullOnDelete();
            $table->enum('device_type', ['main_tank_atg', 'fill_line', 'dispense_line', 'mobile_unit']);
            $table->unsignedBigInteger('tank_id')->nullable();
            $table->enum('status', ['spare', 'active', 'retired'])->default('spare');
            $table->string('api_token_hash')->nullable();
            $table->timestamp('last_seen')->nullable();
            $table->timestamps();

            $table->foreign('tank_id')->references('tank_id')->on('tanks')->nullOnDelete();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('hardware_devices');
    }
};
