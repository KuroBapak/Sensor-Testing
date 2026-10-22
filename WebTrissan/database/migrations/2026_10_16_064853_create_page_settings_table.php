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
        Schema::create('page_settings', function (Blueprint $table) {
            $table->id();
            $table->string('page')->unique(); // 'landing', 'works', 'portfolio', 'licenses', 'about'
            $table->string('seo_title')->nullable();
            $table->text('seo_description')->nullable();
            $table->string('hero_title')->nullable();
            $table->text('hero_subtitle')->nullable();
            $table->string('hero_cta_text')->nullable();
            $table->string('hero_cta_url')->nullable();
            $table->json('hero_slides')->nullable();
            $table->json('trust_card')->nullable();
            $table->json('about_teaser')->nullable();
            $table->json('stats')->nullable();
            $table->json('hero_badge_card')->nullable();
            $table->json('corporate_highlight_cards')->nullable();
            $table->string('registry_revision_tag')->nullable();
            $table->json('extra_data')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('page_settings');
    }
};
