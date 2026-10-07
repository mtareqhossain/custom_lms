<?php

namespace App\Models;

use App\Enums\QuizQuestionType;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class QuizQuestion extends Model
{
    protected $fillable = ['quiz_id', 'type', 'question_text', 'points', 'order_index'];

    public function quiz(): BelongsTo
    {
        return $this->belongsTo(Quiz::class);
    }

    public function options(): HasMany
    {
        return $this->hasMany(QuizQuestionOption::class)->orderBy('order_index');
    }

    public function correctOption(): ?QuizQuestionOption
    {
        return $this->options->firstWhere('is_correct', true);
    }

    public function typeEnum(): QuizQuestionType
    {
        return QuizQuestionType::from($this->type);
    }

    public function isAutoGradable(): bool
    {
        return $this->typeEnum()->isAutoGradable();
    }
}
