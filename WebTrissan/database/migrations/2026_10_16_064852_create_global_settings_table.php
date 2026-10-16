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
        Schema::create('global_settings', function (Blueprint $table) {
            $table->id();
            $table->string('company_name')->default('PT. Apex Industrial Engineering');
            $table->string('company_tagline')->nullable()->default('Industrial Engineering');
            $table->string('navbar_logo')->nullable();
            $table->string('footer_logo')->nullable();
            $table->text('footer_description')->nullable();
            $table->string('footer_partner_image')->nullable();
            $table->string('copyright_text')->nullable();
            $table->json('footer_legal_links')->nullable();
            $table->json('company_stats')->nullable();
            $table->json('phones')->nullable();
            $table->string('primary_email')->nullable();
            $table->string('corporate_email')->nullable();
            $table->text('factory_address')->nullable();
            $table->string('facility_zone')->nullable();
            $table->string('maps_button_url')->nullable();
            $table->text('maps_iframe_embed_url')->nullable();
            $table->string('overlay_title')->nullable();
            $table->string('overlay_coordinate')->nullable();
            $table->json('social_media')->nullable();
            $table->string('notification_email')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('global_settings');
    }
};
