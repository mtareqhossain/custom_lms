<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\Pivot;

class CourseOrganization extends Pivot
{
    protected $table = 'course_organization';

    protected $fillable = ['course_id', 'organization_id', 'seat_limit'];

    public function course(): BelongsTo
    {
        return $this->belongsTo(Course::class);
    }

    public function organization(): BelongsTo
    {
        return $this->belongsTo(Organization::class);
    }
}
