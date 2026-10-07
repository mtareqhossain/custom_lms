<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CertificateSetting extends Model
{
    protected $fillable = ['background_path', 'badge_path'];

    /**
     * Singleton row — there's only ever one global certificate design.
     */
    public static function current(): self
    {
        return static::firstOrCreate(['id' => 1]);
    }
}
