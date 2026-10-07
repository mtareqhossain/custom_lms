<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    // Not used for short_text questions — only multiple_choice / true_false.
    public function up(): void
    {
        Schema::create('quiz_question_options', function (Blueprint $table) {
            $table->id();
            $table->foreignId('quiz_question_id')->constrained()->cascadeOnDelete();
            $table->string('option_text');
            $table->boolean('is_correct')->default(false);
            $table->unsignedInteger('order_index')->default(0);
            $table->timestamps();

            $table->index('quiz_question_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('quiz_question_options');
    }
};
