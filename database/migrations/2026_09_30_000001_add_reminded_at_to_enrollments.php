<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('enrollments', function (Blueprint $t) {
            $t->timestamp('reminded_at')->nullable()->after('completed_at');
        });
    }

    public function down(): void
    {
        Schema::table('enrollments', function (Blueprint $t) {
            $t->dropColumn('reminded_at');
        });
    }
};
