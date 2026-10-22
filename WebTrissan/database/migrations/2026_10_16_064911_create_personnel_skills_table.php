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
        if (! Schema::hasTable('personnel_skill')) {
            Schema::create('personnel_skill', function (Blueprint $table) {
                $table->id();
                $table->unsignedBigInteger('personnel_id');
                $table->unsignedBigInteger('skill_id');
                $table->timestamps();

                $table->foreign('personnel_id')->references('id')->on('personnel')->cascadeOnDelete();
                $table->foreign('skill_id')->references('id')->on('skills')->cascadeOnDelete();
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('personnel_skill');
    }
};
