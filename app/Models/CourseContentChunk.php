<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CourseContentChunk extends Model
{
    protected $fillable = [
        'course_id', 'lesson_id', 'chunk_index', 'chunk_text', 'embedding', 'embedding_model',
    ];

    protected $casts = [
        'embedding' => 'array',
    ];

    public function course(): BelongsTo
    {
        return $this->belongsTo(Course::class);
    }

    public function lesson(): BelongsTo
    {
        return $this->belongsTo(Lesson::class);
    }
}
