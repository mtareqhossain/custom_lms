<?php
namespace App\Models;

use App\Enums\PricingType;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\{BelongsTo, HasMany, HasManyThrough};
use Illuminate\Database\Eloquent\SoftDeletes;

class Course extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'instructor_id', 'category_id', 'title', 'slug', 'summary', 'description', 'thumbnail',
        'intro_video_provider', 'intro_video_ref',
        'level', 'status', 'pricing_type', 'price_cents', 'currency', 'access_days',
        'stripe_price_id', 'published_at', 'certificate_background_path',
        'certificate_badge_path',
        'certificate_eyebrow_font_id', 'certificate_heading_font_id', 'certificate_name_font_id',
        'certificate_course_font_id', 'certificate_meta_font_id'
    ];

    protected $casts = ['pricing_type' => PricingType::class, 'published_at' => 'datetime'];

    public function instructor(): BelongsTo { return $this->belongsTo(User::class, 'instructor_id'); }
    public function category(): BelongsTo { return $this->belongsTo(Category::class); }
    public function sections(): HasMany { return $this->hasMany(Section::class)->orderBy('position'); }
    public function lessons(): HasManyThrough { return $this->hasManyThrough(Lesson::class, Section::class); }
    public function enrollments(): HasMany { return $this->hasMany(Enrollment::class); }

    public function isFree(): bool { return $this->pricing_type === PricingType::Free; }

    /** Expiry for a new enrollment, based on the course's current access_days setting (null = lifetime). */
    public function accessExpiryFrom(?\DateTimeInterface $start = null): ?\Carbon\Carbon
    {
        return $this->access_days ? \Carbon\Carbon::instance($start ?? now())->addDays($this->access_days) : null;
    }

    public function quizzes(): \Illuminate\Database\Eloquent\Relations\HasMany
{
    return $this->hasMany(\App\Models\Quiz::class);
}

public function finalQuizzes(): \Illuminate\Database\Eloquent\Relations\HasMany
{
    return $this->quizzes()->whereNull('lesson_id');
}
}
