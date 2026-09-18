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
        Schema::create('mobile_tank_logs', function (Blueprint $table) {
            $table->id();
            $table->string('tank_type'); // 'fuel_tanker' or 'browser'
            $table->string('rfid');
            $table->string('waktu');
            $table->integer('liter');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('mobile_tank_logs');
    }
};
