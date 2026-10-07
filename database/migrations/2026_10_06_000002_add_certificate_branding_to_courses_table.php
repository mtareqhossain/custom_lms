<?php
// database/migrations/2026_10_06_000002_add_certificate_branding_to_courses_table.php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('courses', function (Blueprint $table) {
            $table->string('certificate_background_path')->nullable();
            $table->string('certificate_badge_path')->nullable();
        });
    }

    public function down(): void
    {
        Schema::table('courses', function (Blueprint $table) {
            $table->dropColumn(['certificate_background_path', 'certificate_badge_path']);
        });
    }
};
