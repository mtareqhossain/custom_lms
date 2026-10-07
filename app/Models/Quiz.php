<?php

namespace App\Models;

use App\Enums\QuizAttemptStatus;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Quiz extends Model
{
    protected $fillable = [
        'course_id', 'lesson_id', 'title', 'description', 'is_required',
        'pass_percentage', 'max_attempts', 'time_limit_minutes', 'is_active', 'created_by',
    ];

    protected $casts = [
        'is_required' => 'boolean',
        'is_active' => 'boolean',
    ];

    public function course(): BelongsTo
    {
        return $this->belongsTo(Course::class);
    }

    public function lesson(): BelongsTo
    {
        return $this->belongsTo(Lesson::class);
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function questions(): HasMany
    {
        return $this->hasMany(QuizQuestion::class)->orderBy('order_index');
    }

    public function attempts(): HasMany
    {
        return $this->hasMany(QuizAttempt::class);
    }

    public function isCourseFinalQuiz(): bool
    {
        return $this->lesson_id === null;
    }

    public function totalPoints(): int
    {
        return (int) $this->questions->sum('points');
    }

    public function attemptsUsedBy(User $user): int
    {
        return $this->attempts()->where('user_id', $user->id)->count();
    }

    public function attemptsRemaining(User $user): ?int
    {
        if ($this->max_attempts === null) {
            return null; // unlimited
        }

        return max(0, $this->max_attempts - $this->attemptsUsedBy($user));
    }

    public function canAttempt(User $user): bool
    {
        if (!$this->is_active) {
            return false;
        }

        return $this->max_attempts === null || $this->attemptsRemaining($user) > 0;
    }

    public function hasPassed(User $user): bool
    {
        return $this->attempts()
            ->where('user_id', $user->id)
            ->where('status', QuizAttemptStatus::Graded->value)
            ->where('passed', true)
            ->exists();
    }

    public function bestAttempt(User $user): ?QuizAttempt
    {
        return $this->attempts()
            ->where('user_id', $user->id)
            ->where('status', QuizAttemptStatus::Graded->value)
            ->orderByDesc('score_percentage')
            ->first();
    }
}
