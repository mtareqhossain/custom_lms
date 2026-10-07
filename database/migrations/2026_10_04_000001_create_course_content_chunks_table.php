<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('course_content_chunks', function (Blueprint $table) {
            $table->id();
            $table->foreignId('course_id')->constrained()->cascadeOnDelete();
            $table->foreignId('lesson_id')->nullable()->constrained()->cascadeOnDelete();
            $table->unsignedInteger('chunk_index')->default(0); // order within the lesson
            $table->text('chunk_text');
            // Embedding vector stored as JSON (e.g. [0.012, -0.034, ...]). At this
            // course volume, pulling all of a course's rows and doing cosine
            // similarity in PHP (see EmbeddingSearch) is simpler and cheaper than
            // standing up a dedicated vector database — revisit only if a single
            // course's content grows into the thousands of chunks.
            $table->json('embedding')->nullable();
            $table->string('embedding_model')->nullable();
            $table->timestamps();

            $table->index(['course_id', 'lesson_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('course_content_chunks');
    }
};
