<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    // Logs chatbot Q&A per student/course — useful for instructors to see what
    // students are confused about, and lets admins audit what the bot answered.
    public function up(): void
    {
        Schema::create('chat_messages', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->foreignId('course_id')->constrained()->cascadeOnDelete();
            $table->text('question');
            $table->text('answer');
            $table->json('source_chunk_ids')->nullable(); // which chunks the answer was grounded in
            $table->timestamps();

            $table->index(['course_id', 'user_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('chat_messages');
    }
};
