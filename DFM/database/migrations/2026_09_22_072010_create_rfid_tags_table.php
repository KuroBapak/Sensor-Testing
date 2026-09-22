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
        Schema::create('rfid_tags', function (Blueprint $table) {
            $table->string('tag_id')->primary();
            $table->unsignedBigInteger('tank_id')->nullable();
            $table->string('sector')->nullable();
            $table->enum('status', ['active', 'blocked'])->default('active');
            $table->timestamps();

            $table->foreign('tank_id')->references('tank_id')->on('tanks')->nullOnDelete();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('rfid_tags');
    }
};
