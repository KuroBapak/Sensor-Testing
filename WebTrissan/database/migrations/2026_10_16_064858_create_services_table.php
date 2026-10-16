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
        Schema::create('services', function (Blueprint $table) {
            $table->id();
            $table->foreignId('service_cluster_id')->constrained()->cascadeOnDelete();
            $table->string('service_title');
            $table->string('short_title');
            $table->string('slug')->unique();
            $table->text('service_description')->nullable();
            $table->string('service_icon')->nullable();
            $table->text('service_icon_svg')->nullable();
            $table->integer('sort_order')->default(0);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('services');
    }
};
