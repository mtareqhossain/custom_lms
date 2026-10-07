<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Database\Factories\UserFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Spatie\Permission\Traits\HasRoles;
use Illuminate\Database\Eloquent\Relations\{BelongsTo, HasMany};
use Filament\Models\Contracts\FilamentUser;
use Filament\Panel;


class User extends Authenticatable implements FilamentUser
{
    /** @use HasFactory<UserFactory> */
    use HasFactory, Notifiable, HasRoles;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'name',
        'email',
        'password',
        'organization_id',
        'is_active',
        'employee_ref',
        'avatar',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var list<string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
        ];
    }
    public function avatarUrl(): ?string
{
    /** @var \Illuminate\Filesystem\FilesystemAdapter $disk */
    $disk = \Illuminate\Support\Facades\Storage::disk('public');

    return $this->avatar ? $disk->url($this->avatar) : null;
}

    public function organization(): BelongsTo {
        return $this->belongsTo(Organization::class);
        }

    public function enrollments(): HasMany {
        return $this->hasMany(Enrollment::class);
        }

    public function taughtCourses(): HasMany {
        return $this->hasMany(Course::class, 'instructor_id');
        }

        public function canAccessPanel(Panel $panel): bool
    {
        return $this->is_active && $this->hasAnyRole(['admin', 'instructor']);
    }
}
