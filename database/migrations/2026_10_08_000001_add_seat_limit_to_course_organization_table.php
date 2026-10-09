<?php
// database/migrations/2026_10_08_000001_add_seat_limit_to_course_organization_table.php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('course_organization', function (Blueprint $table) {
            $table->unsignedInteger('seat_limit')->nullable()->after('course_id');
        });
    }

    public function down(): void
    {
        Schema::table('course_organization', function (Blueprint $table) {
            $table->dropColumn('seat_limit');
        });
    }
};
