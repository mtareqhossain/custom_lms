<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('organizations', function (Blueprint $t) {
            $t->id();
            $t->string('name');
            $t->string('slug')->unique();
            $t->string('contact_email');
            $t->string('logo')->nullable();
            $t->string('stripe_customer_id')->nullable();
            $t->boolean('is_active')->default(true);
            $t->timestamps();
        });

        Schema::table('users', function (Blueprint $t) {
            $t->foreignId('organization_id')->nullable()->after('id')->constrained()->nullOnDelete();
            $t->boolean('is_active')->default(true)->after('password');
            $t->string('employee_ref')->nullable(); // business's own student/employee ID
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $t) {
            $t->dropConstrainedForeignId('organization_id');
            $t->dropColumn(['is_active', 'employee_ref']);
        });
        Schema::dropIfExists('organizations');
    }
};
