<?php
namespace App\Services;

use App\Models\Lesson;
use Illuminate\Support\Facades\Storage;

/** Replaces the Phase 2 resolver: same logic, now usable for lesson videos and course intro videos alike. */
class VideoUrlResolver
{
    /** @return array{type:string,url:string}|null type is "iframe" or "file" */
    public function resolve(Lesson $lesson): ?array
    {
        if ($lesson->type !== 'video' || ! $lesson->video_ref) {
            return null;
        }

        return $this->resolveProvider($lesson->video_provider, $lesson->video_ref);
    }

    /** Same resolution logic, for anything with a provider + ref pair (e.g. a course intro video). */
    public function resolveProvider(?string $provider, ?string $ref): ?array
    {
        if (! $provider || ! $ref) {
            return null;
        }

        return match ($provider) {
            'youtube' => ['type' => 'iframe', 'url' => 'https://www.youtube-nocookie.com/embed/'.$ref],
            'vimeo' => ['type' => 'iframe', 'url' => 'https://player.vimeo.com/video/'.$ref],
            'bunny' => ['type' => 'iframe', 'url' => $this->bunny($ref)],
            's3' => ['type' => 'file', 'url' => Storage::disk('s3')->temporaryUrl($ref, now()->addHours(3))],
            default => null,
        };
    }

    private function bunny(string $videoId): string
    {
        $lib = config('services.bunny.library_id');
        $expires = now()->addHours(3)->timestamp;
        $token = hash('sha256', config('services.bunny.token_key').$videoId.$expires);

        return "https://iframe.mediadelivery.net/embed/{$lib}/{$videoId}?token={$token}&expires={$expires}";
    }
}
