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
        Schema::create('corporate_accreditations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('certification_category_id')->constrained()->cascadeOnDelete();
            $table->string('standard_name');
            $table->text('standard_description')->nullable();
            $table->string('accreditation_body')->nullable();
            $table->string('registration_id')->nullable();
            $table->date('validity_date')->nullable();
            $table->text('scope_detail');
            $table->string('modal_title')->nullable();
            $table->string('certificate_image')->nullable();
            $table->integer('sort_order')->default(0);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('corporate_accreditations');
    }
};
