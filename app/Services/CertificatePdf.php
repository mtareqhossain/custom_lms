<?php
namespace App\Services;

use App\Models\CertificateFont;
use App\Models\CertificateSetting;
use App\Models\Enrollment;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Support\Facades\Storage;

class CertificatePdf
{
    public function render(Enrollment $enrollment): string
    {
        $enrollment->loadMissing('user', 'course', 'certificate');

        $setting = CertificateSetting::current();
        $course = $enrollment->course;

        $backgroundPath = $this->resolveImagePath($course->certificate_background_path, $setting->background_path);
        $badgePath = $this->resolveImagePath($course->certificate_badge_path, $setting->badge_path);

        $fonts = [
            'eyebrow' => $this->resolveFont($course->certificate_eyebrow_font_id, $setting->eyebrow_font_id),
            'heading' => $this->resolveFont($course->certificate_heading_font_id, $setting->heading_font_id),
            'name'    => $this->resolveFont($course->certificate_name_font_id, $setting->name_font_id),
            'course'  => $this->resolveFont($course->certificate_course_font_id, $setting->course_font_id),
            'meta'    => $this->resolveFont($course->certificate_meta_font_id, $setting->meta_font_id),
        ];

        return Pdf::loadView('certificates.pdf', [
            'studentName' => $enrollment->user->name,
            'courseTitle' => $enrollment->course->title,
            'completedAt' => $enrollment->completed_at,
            'code' => $enrollment->certificate->code,
            'backgroundPath' => $backgroundPath,
            'badgePath' => $badgePath,
            'fonts' => $fonts,
        ])
            ->setPaper('a4', 'landscape')
            ->output();
    }

    private function resolveImagePath(?string $courseOverride, ?string $globalSetting): ?string
    {
        $relative = $courseOverride ?: $globalSetting;

        if (!$relative) {
            return null;
        }

        $absolute = Storage::disk('public')->path($relative);

        return file_exists($absolute) ? $absolute : null;
    }

    private function resolveFont(?int $courseOverrideId, ?int $globalId): ?CertificateFont
    {
        $id = $courseOverrideId ?: $globalId;

        return $id ? CertificateFont::find($id) : null;
    }
}
