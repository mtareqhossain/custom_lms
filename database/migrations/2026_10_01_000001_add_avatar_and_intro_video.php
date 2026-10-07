<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('users', function (Blueprint $t) {
            $t->string('avatar')->nullable()->after('name');
        });
        Schema::table('courses', function (Blueprint $t) {
            $t->string('intro_video_provider')->nullable()->after('thumbnail');
            $t->string('intro_video_ref')->nullable()->after('intro_video_provider');
        });
    }

    public function down(): void
    {
        Schema::table('users', fn (Blueprint $t) => $t->dropColumn('avatar'));
        Schema::table('courses', fn (Blueprint $t) => $t->dropColumn(['intro_video_provider', 'intro_video_ref']));
    }
};
