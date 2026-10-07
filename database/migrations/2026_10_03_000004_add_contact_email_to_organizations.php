<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    // Business-completion notifications need somewhere to send to. If your
    // organizations table already has a contact email column under a
    // different name, skip this migration and adjust
    // app/Services/ProgressService.php to use your existing column instead.
    public function up(): void
    {
        Schema::table('organizations', function (Blueprint $table) {
            if (!Schema::hasColumn('organizations', 'contact_email')) {
                $table->string('contact_email')->nullable()->after('name');
            }
            if (!Schema::hasColumn('organizations', 'contact_name')) {
                $table->string('contact_name')->nullable()->after('contact_email');
            }
        });
    }

    public function down(): void
    {
        Schema::table('organizations', function (Blueprint $table) {
            $table->dropColumn(['contact_email', 'contact_name']);
        });
    }
};
