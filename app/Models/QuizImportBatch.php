<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class QuizImportBatch extends Model
{
    protected $fillable = [
        'file_path', 'status', 'quizzes_created', 'questions_created',
        'failed_rows', 'errors', 'created_by',
    ];

    protected $casts = ['errors' => 'array'];

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }
}
