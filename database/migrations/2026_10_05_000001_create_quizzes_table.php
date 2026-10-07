<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('quizzes', function (Blueprint $table) {
            $table->id();
            $table->foreignId('course_id')->constrained()->cascadeOnDelete();
            // Null lesson_id = a course-final quiz (final exam), not tied to one lesson.
            $table->foreignId('lesson_id')->nullable()->constrained()->cascadeOnDelete();
            $table->string('title');
            $table->text('description')->nullable();
            // If true, the student must pass this quiz (score >= pass_percentage)
            // before the course can be marked completed / certificate issued.
            $table->boolean('is_required')->default(false);
            $table->unsignedTinyInteger('pass_percentage')->default(70);
            // Null = unlimited attempts.
            $table->unsignedInteger('max_attempts')->nullable();
            $table->unsignedInteger('time_limit_minutes')->nullable();
            $table->boolean('is_active')->default(true);
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();

            $table->index(['course_id', 'lesson_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('quizzes');
    }
};
