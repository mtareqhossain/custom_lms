<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class TrainingAssignment extends Model
{
    protected $fillable = [
        'organization_id', 'course_id', 'user_id', 'assigned_by_id', 'enrollment_id',
        'due_at', 'recurrence', 'completed_at', 'last_reminder_at', 'renewed_at',
    ];

    protected $casts = [
        'due_at' => 'datetime', 'completed_at' => 'datetime',
        'last_reminder_at' => 'datetime', 'renewed_at' => 'datetime',
    ];

    public const RECURRENCES = [
        'monthly' => 'Monthly', 'quarterly' => 'Quarterly',
        'semiannual' => 'Every 6 months', 'annual' => 'Annually',
    ];

    public function organization(): BelongsTo { return $this->belongsTo(Organization::class); }
    public function course(): BelongsTo { return $this->belongsTo(Course::class); }
    public function user(): BelongsTo { return $this->belongsTo(User::class); }
    public function assignedBy(): BelongsTo { return $this->belongsTo(User::class, 'assigned_by_id'); }
    public function enrollment(): BelongsTo { return $this->belongsTo(Enrollment::class); }

    public function status(): string
    {
        if ($this->completed_at) return 'completed';
        if (! $this->due_at) return 'assigned';
        if ($this->due_at->isPast()) return 'overdue';
        if ($this->due_at->lte(now()->addDays(3))) return 'due_soon';
        return 'assigned';
    }

    /** Next due date for a recurring cycle, counted from when the previous cycle completed. */
    public function nextDueDate(\DateTimeInterface $from): ?\Carbon\Carbon
    {
        $date = \Carbon\Carbon::instance($from);

        return match ($this->recurrence) {
            'monthly' => $date->copy()->addMonth(),
            'quarterly' => $date->copy()->addMonths(3),
            'semiannual' => $date->copy()->addMonths(6),
            'annual' => $date->copy()->addYear(),
            default => null,
        };
    }
}
