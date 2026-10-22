<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('tanks', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->decimal('capacity_liters', 10, 2);
            $table->timestamps();
        });

        Schema::create('tank_readings', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tank_id')->constrained()->cascadeOnDelete();
            $table->dateTime('read_at');
            $table->decimal('total_pct', 5, 2);
            $table->decimal('fuel_pct', 5, 2);
            $table->decimal('water_pct', 5, 2);

            $table->unique(['tank_id', 'read_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('tank_readings');
        Schema::dropIfExists('tanks');
    }
};
