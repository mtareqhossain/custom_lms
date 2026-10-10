<?php
// app/Models/SiteSetting.php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Cache;

class SiteSetting extends Model
{
    protected $fillable = ['site_name', 'logo_path'];

    public static function current(): self
    {
        return Cache::rememberForever('site_settings', function () {
            return self::firstOrCreate(['id' => 1], ['site_name' => config('app.name')]);
        });
    }

    protected static function booted()
    {
        static::saved(fn () => Cache::forget('site_settings'));
    }
}
