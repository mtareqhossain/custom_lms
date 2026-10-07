<?php
// database/migrations/2026_10_06_000004_add_fonts_to_certificate_settings_table.php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('certificate_settings', function (Blueprint $table) {
            $table->foreignId('eyebrow_font_id')->nullable()->constrained('certificate_fonts')->nullOnDelete();
            $table->foreignId('heading_font_id')->nullable()->constrained('certificate_fonts')->nullOnDelete();
            $table->foreignId('name_font_id')->nullable()->constrained('certificate_fonts')->nullOnDelete();
            $table->foreignId('course_font_id')->nullable()->constrained('certificate_fonts')->nullOnDelete();
            $table->foreignId('meta_font_id')->nullable()->constrained('certificate_fonts')->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('certificate_settings', function (Blueprint $table) {
            $table->dropConstrainedForeignId('eyebrow_font_id');
            $table->dropConstrainedForeignId('heading_font_id');
            $table->dropConstrainedForeignId('name_font_id');
            $table->dropConstrainedForeignId('course_font_id');
            $table->dropConstrainedForeignId('meta_font_id');
        });
    }
};
