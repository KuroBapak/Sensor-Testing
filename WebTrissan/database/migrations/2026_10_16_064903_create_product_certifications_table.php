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
        Schema::create('product_certifications', function (Blueprint $table) {
            $table->id();
            $table->foreignId('work_id')->nullable()->constrained('works')->nullOnDelete();
            $table->string('slug')->unique();
            $table->string('category_tag')->nullable();
            $table->string('category_icon')->nullable();
            $table->boolean('is_featured')->default(false);
            $table->date('validity_date')->nullable();
            $table->string('product_name');
            $table->string('subtitle')->nullable();
            $table->string('model_spec_identifier')->nullable();
            $table->string('card_icon')->nullable();
            $table->string('certificate_number')->nullable();
            $table->string('issuing_body')->nullable();
            $table->json('spec_rows')->nullable();
            $table->text('scope_description')->nullable();
            $table->string('certificate_image')->nullable();
            $table->string('certificate_pdf')->nullable();
            $table->integer('sort_order')->default(0);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('product_certifications');
    }
};
