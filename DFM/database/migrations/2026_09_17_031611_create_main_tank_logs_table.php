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
        Schema::create('main_tank_logs', function (Blueprint $table) {
            $table->id();
            $table->string('waktu');
            $table->integer('total_liter');
            $table->integer('liter_masuk');
            $table->integer('liter_keluar');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('main_tank_logs');
    }
};
