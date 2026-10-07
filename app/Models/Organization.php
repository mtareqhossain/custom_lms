<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\{BelongsToMany, HasMany};

class Organization extends Model
{
    protected $fillable = ['name', 'slug', 'contact_email', 'logo', 'stripe_customer_id', 'is_active'];

    protected $casts = ['is_active' => 'boolean'];

    public function users(): HasMany { return $this->hasMany(User::class); }
    public function enrollments(): HasMany { return $this->hasMany(Enrollment::class); }

    /** Courses this organization is allowed to enroll students in (pivot: course_organization). */
    public function courses(): BelongsToMany { return $this->belongsToMany(Course::class); }
}
