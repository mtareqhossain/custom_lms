<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            // Default true so existing admin/business accounts are protected immediately
            // after this migration runs. Students/instructors can be left on or switched
            // off per-user from the admin panel.
            $table->boolean('two_factor_enabled')->default(false)->after('remember_token');
            $table->string('two_factor_code', 10)->nullable()->after('two_factor_enabled');
            $table->timestamp('two_factor_expires_at')->nullable()->after('two_factor_code');
            $table->unsignedTinyInteger('two_factor_attempts')->default(0)->after('two_factor_expires_at');
        });

        // Turn 2FA on by default for the higher-risk roles (admin, business).
        // Safe no-op if the roles/model_has_roles tables are empty at migration time.
        if (Schema::hasTable('roles') && Schema::hasTable('model_has_roles')) {
            $roleIds = \DB::table('roles')->whereIn('name', ['admin', 'business'])->pluck('id');
            if ($roleIds->isNotEmpty()) {
                $userIds = \DB::table('model_has_roles')
                    ->where('model_type', 'App\\Models\\User')
                    ->whereIn('role_id', $roleIds)
                    ->pluck('model_id');
                if ($userIds->isNotEmpty()) {
                    \DB::table('users')->whereIn('id', $userIds)->update(['two_factor_enabled' => true]);
                }
            }
        }
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn(['two_factor_enabled', 'two_factor_code', 'two_factor_expires_at', 'two_factor_attempts']);
        });
    }
};
