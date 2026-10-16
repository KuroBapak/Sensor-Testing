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
        Schema::create('works', function (Blueprint $table) {
            $table->id();
            $table->foreignId('service_id')->constrained()->cascadeOnDelete();
            $table->string('work_title');
            $table->string('work_slug')->unique();
            $table->boolean('is_featured')->default(false);
            $table->integer('featured_sort_order')->default(0);
            $table->string('work_image')->nullable();
            $table->json('gallery_images')->nullable();
            $table->string('top_badge')->nullable();
            $table->string('bottom_badge')->nullable();
            $table->string('serial_number')->nullable();
            $table->string('client_sector')->nullable();
            $table->text('short_description')->nullable();
            $table->text('full_description')->nullable();
            $table->json('highlight_tags')->nullable();
            $table->json('quality_standards')->nullable();
            $table->string('material')->nullable();
            $table->string('tolerance_specs')->nullable();
            $table->decimal('tolerance_value', 8, 4)->nullable();
            $table->string('batch_scale')->nullable();
            $table->integer('scale_value')->nullable();
            $table->date('production_date')->nullable();
            $table->integer('sort_order')->default(0);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('works');
    }
};
