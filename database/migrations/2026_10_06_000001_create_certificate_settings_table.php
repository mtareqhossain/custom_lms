<?php
// database/migrations/2026_10_06_000001_create_certificate_settings_table.php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('certificate_settings', function (Blueprint $table) {
            $table->id();
            $table->string('background_path')->nullable();
            $table->string('badge_path')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('certificate_settings');
    }
};
