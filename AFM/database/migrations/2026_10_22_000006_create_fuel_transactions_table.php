<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('fuel_transactions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('qr_card_id')->constrained()->cascadeOnDelete();
            $table->decimal('liters', 10, 2);
            $table->dateTime('dispensed_at');
            $table->string('source', 16)->default('dummy');
            // Phase 2: set when dispensers are connected. Nullable so phase 1 rows stay valid.
            $table->unsignedBigInteger('dispenser_id')->nullable();
            $table->timestamp('created_at')->nullable();

            $table->index(['qr_card_id', 'dispensed_at']);
            $table->index('dispensed_at');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('fuel_transactions');
    }
};
