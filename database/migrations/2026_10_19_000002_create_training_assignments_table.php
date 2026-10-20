<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('training_assignments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('organization_id')->constrained()->cascadeOnDelete();
            $table->foreignId('course_id')->constrained()->cascadeOnDelete();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete(); // assignee
            $table->foreignId('assigned_by_id')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('enrollment_id')->nullable()->constrained()->nullOnDelete();
            $table->timestamp('due_at')->nullable();
            $table->string('recurrence')->nullable(); // monthly, quarterly, semiannual, annual
            $table->timestamp('completed_at')->nullable();
            $table->timestamp('last_reminder_at')->nullable();
            $table->timestamp('renewed_at')->nullable(); // set once the next recurring cycle is spawned
            $table->timestamps();

            $table->index(['organization_id', 'user_id']);
            $table->index('due_at');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('training_assignments');
    }
};
