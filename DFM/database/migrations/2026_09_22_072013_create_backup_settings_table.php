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
        Schema::create('backup_settings', function (Blueprint $table) {
            $table->id();
            $table->foreignId('site_id')->constrained('sites')->cascadeOnDelete();
            $table->string('provider')->default('S3-compatible');
            $table->string('endpoint')->nullable();
            $table->string('region')->default('us-east-1');
            $table->string('bucket')->nullable();
            $table->string('path_prefix')->nullable();
            $table->boolean('use_path_style')->default(true);
            $table->text('access_key_id')->nullable();
            $table->text('secret_access_key')->nullable();
            $table->time('schedule_time')->default('02:00:00');
            $table->integer('retention_days')->default(30);
            $table->boolean('enabled')->default(false);
            $table->timestamp('last_connection_test_at')->nullable();
            $table->boolean('last_connection_test_ok')->nullable();
            $table->foreignId('updated_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('backup_settings');
    }
};
