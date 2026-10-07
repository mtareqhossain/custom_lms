<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class QuizAttempt extends Model
{
    protected $fillable = [
        'quiz_id', 'user_id', 'enrollment_id', 'attempt_number', 'status',
        'score_percentage', 'passed', 'started_at', 'submitted_at', 'graded_at', 'graded_by',
    ];

    protected $casts = [
        'passed' => 'boolean',
        'score_percentage' => 'float',
        'started_at' => 'datetime',
        'submitted_at' => 'datetime',
        'graded_at' => 'datetime',
    ];

    public function quiz(): BelongsTo
    {
        return $this->belongsTo(Quiz::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function enrollment(): BelongsTo
    {
        return $this->belongsTo(Enrollment::class);
    }

    public function grader(): BelongsTo
    {
        return $this->belongsTo(User::class, 'graded_by');
    }

    public function answers(): HasMany
    {
        return $this->hasMany(QuizAttemptAnswer::class);
    }
}
