<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('categories', function (Blueprint $t) {
            $t->id();
            $t->string('name');
            $t->string('slug')->unique();
            $t->timestamps();
        });

        Schema::create('courses', function (Blueprint $t) {
            $t->id();
            $t->foreignId('instructor_id')->constrained('users')->cascadeOnDelete();
            $t->foreignId('category_id')->nullable()->constrained()->nullOnDelete();
            $t->string('title');
            $t->string('slug')->unique();
            $t->text('summary')->nullable();
            $t->longText('description')->nullable();
            $t->string('thumbnail')->nullable();
            $t->enum('level', ['beginner', 'intermediate', 'advanced'])->default('beginner');
            $t->enum('status', ['draft', 'published', 'archived'])->default('draft');
            $t->enum('pricing_type', ['free', 'paid'])->default('free');
            $t->unsignedInteger('price_cents')->default(0);
            $t->char('currency', 3)->default('GBP');
            $t->unsignedInteger('access_days')->nullable(); // null = lifetime; admin editable
            $t->string('stripe_price_id')->nullable();
            $t->timestamp('published_at')->nullable();
            $t->timestamps();
            $t->softDeletes();
            $t->index(['status', 'pricing_type']);
        });

        Schema::create('sections', function (Blueprint $t) {
            $t->id();
            $t->foreignId('course_id')->constrained()->cascadeOnDelete();
            $t->string('title');
            $t->unsignedInteger('position')->default(0);
            $t->timestamps();
        });

        Schema::create('lessons', function (Blueprint $t) {
            $t->id();
            $t->foreignId('section_id')->constrained()->cascadeOnDelete();
            $t->string('title');
            $t->enum('type', ['video', 'text', 'file', 'quiz'])->default('video');
            $t->string('video_provider')->nullable(); // bunny, s3, youtube, vimeo
            $t->string('video_ref')->nullable();      // provider video id / path
            $t->longText('content')->nullable();
            $t->string('attachment')->nullable();
            $t->unsignedInteger('duration_seconds')->default(0);
            $t->unsignedInteger('position')->default(0);
            $t->boolean('is_preview')->default(false);
            $t->timestamps();
        });
    }

    public function down(): void
    {
        foreach (['lessons', 'sections', 'courses', 'categories'] as $tbl) Schema::dropIfExists($tbl);
    }
};
