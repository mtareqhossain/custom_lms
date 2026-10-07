<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('quiz_attempt_answers', function (Blueprint $table) {
            $table->id();
            $table->foreignId('quiz_attempt_id')->constrained()->cascadeOnDelete();
            $table->foreignId('quiz_question_id')->constrained()->cascadeOnDelete();
            $table->foreignId('selected_option_id')->nullable()->constrained('quiz_question_options')->nullOnDelete();
            $table->text('short_text_answer')->nullable();
            // Null until graded (always true for short_text until an instructor grades it;
            // set immediately on submit for auto-graded multiple_choice/true_false).
            $table->boolean('is_correct')->nullable();
            $table->decimal('points_awarded', 5, 2)->nullable();
            $table->timestamps();

            $table->index('quiz_attempt_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('quiz_attempt_answers');
    }
};
