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
        Schema::table('services', function (Blueprint $table) {
            $table->text('landing_body')->nullable()->after('service_description');
            $table->json('process_steps')->nullable()->after('landing_body');
            $table->json('faqs')->nullable()->after('process_steps');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('services', function (Blueprint $table) {
            $table->dropColumn(['landing_body', 'process_steps', 'faqs']);
        });
    }
};
