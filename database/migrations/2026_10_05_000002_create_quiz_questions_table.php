<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('quiz_questions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('quiz_id')->constrained()->cascadeOnDelete();
            $table->string('type'); // multiple_choice | true_false | short_text
            $table->text('question_text');
            $table->unsignedInteger('points')->default(1);
            $table->unsignedInteger('order_index')->default(0);
            $table->timestamps();

            $table->index(['quiz_id', 'order_index']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('quiz_questions');
    }
};
