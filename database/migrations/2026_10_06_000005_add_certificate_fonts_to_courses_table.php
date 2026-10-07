<?php
// database/migrations/2026_10_06_000005_add_certificate_fonts_to_courses_table.php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('courses', function (Blueprint $table) {
            $table->foreignId('certificate_eyebrow_font_id')->nullable()->constrained('certificate_fonts')->nullOnDelete();
            $table->foreignId('certificate_heading_font_id')->nullable()->constrained('certificate_fonts')->nullOnDelete();
            $table->foreignId('certificate_name_font_id')->nullable()->constrained('certificate_fonts')->nullOnDelete();
            $table->foreignId('certificate_course_font_id')->nullable()->constrained('certificate_fonts')->nullOnDelete();
            $table->foreignId('certificate_meta_font_id')->nullable()->constrained('certificate_fonts')->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('courses', function (Blueprint $table) {
            $table->dropConstrainedForeignId('certificate_eyebrow_font_id');
            $table->dropConstrainedForeignId('certificate_heading_font_id');
            $table->dropConstrainedForeignId('certificate_name_font_id');
            $table->dropConstrainedForeignId('certificate_course_font_id');
            $table->dropConstrainedForeignId('certificate_meta_font_id');
        });
    }
};
