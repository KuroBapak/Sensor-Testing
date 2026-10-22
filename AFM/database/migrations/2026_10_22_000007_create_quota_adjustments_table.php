<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('quota_adjustments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('qr_card_id')->constrained()->cascadeOnDelete();
            $table->decimal('liters', 10, 2);
            $table->string('note')->nullable();
            $table->foreignId('user_id')->nullable()->constrained()->nullOnDelete();
            $table->timestamp('created_at')->nullable();

            $table->index(['qr_card_id', 'created_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('quota_adjustments');
    }
};
