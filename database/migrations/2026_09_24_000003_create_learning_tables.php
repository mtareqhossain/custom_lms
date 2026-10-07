<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('enrollments', function (Blueprint $t) {
            $t->id();
            $t->foreignId('user_id')->constrained()->cascadeOnDelete();
            $t->foreignId('course_id')->constrained()->cascadeOnDelete();
            $t->foreignId('organization_id')->nullable()->constrained()->nullOnDelete();
            $t->enum('source', ['free', 'purchase', 'business', 'admin'])->default('free');
            $t->timestamp('starts_at')->useCurrent();
            $t->timestamp('expires_at')->nullable(); // null = never expires
            $t->unsignedTinyInteger('progress_percent')->default(0);
            $t->timestamp('completed_at')->nullable();
            $t->timestamps();
            $t->unique(['user_id', 'course_id']);
            $t->index(['organization_id', 'course_id']);
        });

        Schema::create('lesson_progress', function (Blueprint $t) {
            $t->id();
            $t->foreignId('enrollment_id')->constrained()->cascadeOnDelete();
            $t->foreignId('lesson_id')->constrained()->cascadeOnDelete();
            $t->unsignedInteger('seconds_watched')->default(0);
            $t->timestamp('completed_at')->nullable();
            $t->timestamps();
            $t->unique(['enrollment_id', 'lesson_id']);
        });

        Schema::create('certificates', function (Blueprint $t) {
            $t->id();
            $t->foreignId('enrollment_id')->unique()->constrained()->cascadeOnDelete();
            $t->uuid('code')->unique(); // public verification code
            $t->timestamp('issued_at');
            $t->timestamps();
        });

        Schema::create('bulk_imports', function (Blueprint $t) {
            $t->id();
            $t->foreignId('user_id')->constrained()->cascadeOnDelete();
            $t->foreignId('organization_id')->nullable()->constrained()->nullOnDelete();
            $t->enum('type', ['students', 'courses', 'lessons']);
            $t->string('file_path');
            $t->enum('status', ['pending', 'processing', 'completed', 'failed'])->default('pending');
            $t->unsignedInteger('total_rows')->default(0);
            $t->unsignedInteger('processed_rows')->default(0);
            $t->unsignedInteger('failed_rows')->default(0);
            $t->json('errors')->nullable();
            $t->timestamps();
        });
    }

    public function down(): void
    {
        foreach (['bulk_imports', 'certificates', 'lesson_progress', 'enrollments'] as $tbl) Schema::dropIfExists($tbl);
    }
};
