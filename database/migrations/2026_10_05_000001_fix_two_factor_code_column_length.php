<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    // Phase 17's original migration created two_factor_code as VARCHAR(10),
    // but the code stores a bcrypt hash of the code (always 60 characters),
    // causing "Data too long for column" on every 2FA login attempt.
    //
    // Uses raw SQL rather than Schema::table(...)->change() so it doesn't
    // require the doctrine/dbal package to be installed.
    public function up(): void
    {
        DB::statement('ALTER TABLE users MODIFY two_factor_code VARCHAR(255) NULL');
    }

    public function down(): void
    {
        DB::statement('ALTER TABLE users MODIFY two_factor_code VARCHAR(10) NULL');
    }
};
