<?php
namespace App\Models;

use App\Enums\EnrollmentSource;
use App\Exceptions\SeatLimitExceededException;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\{BelongsTo, HasMany, HasOne};

class Enrollment extends Model
{
    protected $fillable = [
        'user_id', 'course_id', 'organization_id', 'source', 'starts_at', 'expires_at',
        'progress_percent', 'completed_at',
    ];

    protected $casts = [
        'source' => EnrollmentSource::class,
        'starts_at' => 'datetime', 'expires_at' => 'datetime', 'completed_at' => 'datetime',
    ];

    public function user(): BelongsTo { return $this->belongsTo(User::class); }
    public function course(): BelongsTo { return $this->belongsTo(Course::class); }
    public function organization(): BelongsTo { return $this->belongsTo(Organization::class); }
    public function progress(): HasMany { return $this->hasMany(LessonProgress::class); }
    public function certificate(): HasOne { return $this->hasOne(Certificate::class); }

    public function isActive(): bool { return $this->expires_at === null || $this->expires_at->isFuture(); }

    /** Single entry point so purchases, business bulk uploads and admin grants all behave the same. */
    public static function grant(User $user, Course $course, EnrollmentSource $source, ?int $orgId = null): self
    {
        $existing = static::where('user_id', $user->id)->where('course_id', $course->id)->first();
        if ($existing) {
            return $existing;
        }

        if ($orgId) {
            $seatLimit = CourseOrganization::where('course_id', $course->id)
                ->where('organization_id', $orgId)
                ->value('seat_limit');

            if ($seatLimit !== null) {
                $used = static::where('course_id', $course->id)->where('organization_id', $orgId)->count();

                if ($used >= $seatLimit) {
                    throw new SeatLimitExceededException(
                        "This organization has used all {$seatLimit} seat(s) for \"{$course->title}\"."
                    );
                }
            }
        }

        return static::create([
            'user_id' => $user->id,
            'course_id' => $course->id,
            'organization_id' => $orgId,
            'source' => $source,
            'starts_at' => now(),
            'expires_at' => $course->accessExpiryFrom(),
        ]);
    }
}
