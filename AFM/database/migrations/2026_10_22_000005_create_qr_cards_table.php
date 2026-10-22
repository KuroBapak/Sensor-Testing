<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('qr_cards', function (Blueprint $table) {
            $table->id();
            $table->string('token', 64)->unique();
            $table->string('category', 16)->index();
            $table->foreignId('vehicle_type_id')->constrained()->restrictOnDelete();
            $table->string('plate_number', 20)->index();
            $table->string('validity', 16);
            $table->unsignedSmallInteger('valid_days')->nullable();
            $table->dateTime('issued_at');
            $table->dateTime('expires_at')->nullable()->index();
            $table->boolean('reset_enabled')->default(true);
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
            $table->softDeletes();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('qr_cards');
    }
};
