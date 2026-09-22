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
        Schema::create('tank_geofence_states', function (Blueprint $table) {
            $table->unsignedBigInteger('tank_id');
            $table->unsignedBigInteger('geofence_id');
            $table->boolean('is_inside');
            $table->integer('consecutive_count')->default(0);
            $table->timestamps();

            $table->primary(['tank_id', 'geofence_id']);
            $table->foreign('tank_id')->references('tank_id')->on('tanks')->cascadeOnDelete();
            $table->foreign('geofence_id')->references('id')->on('geofences')->cascadeOnDelete();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('tank_geofence_states');
    }
};
