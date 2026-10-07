<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
class Lesson extends Model
{
    protected $fillable = ['section_id', 'title', 'type', 'video_provider', 'video_ref', 'content',
        'attachment', 'duration_seconds', 'position', 'is_preview'];
    protected $casts = ['is_preview' => 'boolean'];
    public function section(): BelongsTo { return $this->belongsTo(Section::class); }
    public function quizzes(): \Illuminate\Database\Eloquent\Relations\HasMany
{
    return $this->hasMany(\App\Models\Quiz::class);
}
}
