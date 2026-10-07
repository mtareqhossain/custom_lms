<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/** Singleton content row for the homepage — always exactly one record, id 1. */
class HomepageContent extends Model
{
    protected $table = 'homepage_content';

    protected $fillable = [
        'hero_headline', 'hero_subheadline', 'hero_image',
        'business_headline', 'business_subheadline', 'business_features',
        'feature_strip', 'why_us_points',
        'instructor_cta_headline', 'instructor_cta_text',
        'client_logos', 'testimonials', 'certifications',
        'final_cta_headline', 'final_cta_subheadline',
    ];

    protected $casts = [
        'business_features' => 'array',
        'feature_strip' => 'array',
        'why_us_points' => 'array',
        'client_logos' => 'array',
        'testimonials' => 'array',
        'certifications' => 'array',
    ];

    public static function current(): self
    {
        return static::findOrNew(1);
    }
}
