<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    // Mirrors the existing bulk_imports table's shape (Phase 4/5) but kept
    // separate so this phase doesn't need to guess at / modify your existing
    // BulkImport model's schema.
    public function up(): void
    {
        Schema::create('quiz_import_batches', function (Blueprint $table) {
            $table->id();
            $table->string('file_path');
            $table->string('status')->default('pending'); // pending, processing, completed, failed
            $table->unsignedInteger('quizzes_created')->default(0);
            $table->unsignedInteger('questions_created')->default(0);
            $table->unsignedInteger('failed_rows')->default(0);
            $table->json('errors')->nullable();
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('quiz_import_batches');
    }
};
