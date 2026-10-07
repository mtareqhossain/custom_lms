<?php
namespace App\Jobs;

use App\Imports\HeadingsImport;
use App\Models\BulkImport;
use App\Models\Category;
use App\Models\Course;
use App\Models\Lesson;
use App\Models\Section;
use App\Models\User;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Maatwebsite\Excel\Facades\Excel;
use ZipArchive;

/** One spreadsheet row = one lesson. Creates/updates course, section and lesson. Safe to re-run. */
class ImportContentJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, SerializesModels;

    public int $tries = 1;
    public int $timeout = 900;

    private const ALLOWED_MEDIA = ['jpg', 'jpeg', 'png', 'webp', 'gif', 'pdf', 'doc', 'docx', 'ppt', 'pptx', 'xls', 'xlsx', 'txt'];

    private ?ZipArchive $zip = null;
    /** @var array<string,string> lowercase basename => path inside the ZIP */
    private array $media = [];

    public function __construct(private readonly int $bulkImportId, private readonly ?string $mediaZipPath = null) {}

    public function handle(): void
    {
        $import = BulkImport::findOrFail($this->bulkImportId);
        $import->update(['status' => 'processing']);

        try {
            $rows = Excel::toCollection(new HeadingsImport, Storage::disk('local')->path($import->file_path))->first() ?? collect();
            $this->openZip();
        } catch (\Throwable $e) {
            $import->update(['status' => 'failed', 'errors' => [['row' => 0, 'error' => 'Could not read the file: '.$e->getMessage()]]]);
            return;
        }

        $errors = [];
        $ok = 0;
        $failed = 0;

        foreach ($rows as $i => $row) {
            if ($row->filter(fn ($v) => $v !== null && $v !== '')->isEmpty()) {
                continue;
            }

            try {
                DB::transaction(fn () => $this->importRow($row, $import));
                $ok++;
            } catch (\Throwable $e) {
                $failed++;
                if (count($errors) < 100) {
                    $errors[] = ['row' => $i + 2, 'error' => $e->getMessage()];
                }
            }
        }

        $this->zip?->close();

        $import->update([
            'status' => 'completed',
            'total_rows' => $ok + $failed,
            'processed_rows' => $ok,
            'failed_rows' => $failed,
            'errors' => $errors,
        ]);
    }

    private function openZip(): void
    {
        if (! $this->mediaZipPath) {
            return;
        }
        $zip = new ZipArchive();
        if ($zip->open(Storage::disk('local')->path($this->mediaZipPath)) !== true) {
            throw new \RuntimeException('The media ZIP could not be opened.');
        }
        for ($n = 0; $n < $zip->numFiles; $n++) {
            $name = $zip->getNameIndex($n);
            if ($name !== false && ! str_ends_with($name, '/')) {
                $this->media[strtolower(basename($name))] = $name;
            }
        }
        $this->zip = $zip;
    }

    private function importRow($row, BulkImport $import): void
    {
        $get = function (string $key) use ($row): ?string {
            $v = trim((string) ($row[$key] ?? ''));
            return $v === '' ? null : $v;
        };

        $slug = Str::slug($get('course_slug') ?? '');
        $sectionTitle = $get('section');
        $lessonTitle = $get('lesson_title');
        if (! $slug || ! $sectionTitle || ! $lessonTitle) {
            throw new \InvalidArgumentException('course_slug, section and lesson_title are required');
        }

        $checks = [
            'level' => ['beginner', 'intermediate', 'advanced'],
            'status' => ['draft', 'published', 'archived'],
            'pricing_type' => ['free', 'paid'],
            'lesson_type' => ['video', 'text', 'file'],
            'video_provider' => ['youtube', 'vimeo', 'bunny', 's3'],
        ];
        foreach ($checks as $col => $allowed) {
            $val = $get($col);
            if ($val !== null && ! in_array(strtolower($val), $allowed, true)) {
                throw new \InvalidArgumentException("$col must be one of: ".implode(', ', $allowed));
            }
        }

        $course = $this->upsertCourse($slug, $get, $import);

        $section = Section::where('course_id', $course->id)->where('title', $sectionTitle)->first()
            ?? Section::create([
                'course_id' => $course->id,
                'title' => $sectionTitle,
                'position' => (int) Section::where('course_id', $course->id)->max('position') + 1,
            ]);

        $type = $get('lesson_type') ? strtolower($get('lesson_type')) : ($get('video_ref') ? 'video' : 'text');
        if ($type === 'video' && (! $get('video_provider') || ! $get('video_ref'))) {
            throw new \InvalidArgumentException('Video lessons need video_provider and video_ref');
        }

        $lesson = Lesson::firstOrNew(['section_id' => $section->id, 'title' => $lessonTitle]);
        if (! $lesson->exists) {
            $lesson->position = (int) Lesson::where('section_id', $section->id)->max('position') + 1;
        }
        $lesson->fill([
            'type' => $type,
            'video_provider' => $type === 'video' ? strtolower($get('video_provider')) : null,
            'video_ref' => $type === 'video' ? $get('video_ref') : null,
            'content' => $get('content'),
            'duration_seconds' => (int) ($get('duration_seconds') ?? 0),
            'is_preview' => in_array(strtolower($get('is_preview') ?? ''), ['1', 'yes', 'true', 'y'], true),
        ]);
        if ($get('attachment_file')) {
            $lesson->attachment = $this->storeMedia($get('attachment_file'), 'attachments');
        }
        $lesson->save();
    }

    private function upsertCourse(string $slug, \Closure $get, BulkImport $import): Course
    {
        $course = Course::where('slug', $slug)->first();

        $attrs = array_filter([
            'title' => $get('course_title'),
            'summary' => $get('course_summary'),
            'level' => $get('level') ? strtolower($get('level')) : null,
            'status' => $get('status') ? strtolower($get('status')) : null,
            'pricing_type' => $get('pricing_type') ? strtolower($get('pricing_type')) : null,
            'currency' => $get('currency') ? strtoupper(substr($get('currency'), 0, 3)) : null,
        ], fn ($v) => $v !== null);

        if ($get('price') !== null) {
            $attrs['price_cents'] = (int) round(((float) $get('price')) * 100);
        }
        if ($get('access_days') !== null) {
            $attrs['access_days'] = ((int) $get('access_days')) ?: null;
        }
        if ($get('instructor_email')) {
            $instructor = User::where('email', strtolower($get('instructor_email')))->first();
            if (! $instructor || ! $instructor->hasAnyRole(['instructor', 'admin'])) {
                throw new \InvalidArgumentException('instructor_email is not an instructor or admin account');
            }
            $attrs['instructor_id'] = $instructor->id;
        }
        if ($get('category')) {
            $attrs['category_id'] = Category::firstOrCreate(
                ['slug' => Str::slug($get('category'))],
                ['name' => $get('category')],
            )->id;
        }
        if ($get('thumbnail_file')) {
            $attrs['thumbnail'] = $this->storeMedia($get('thumbnail_file'), 'thumbnails');
        }
        if (($attrs['status'] ?? null) === 'published' && (! $course || ! $course->published_at)) {
            $attrs['published_at'] = now();
        }
        if (($attrs['pricing_type'] ?? null) === 'free') {
            $attrs['price_cents'] = 0;
        }

        if (! $course) {
            if (empty($attrs['title'])) {
                throw new \InvalidArgumentException("New course '$slug' needs a course_title on its first row");
            }
            $attrs += ['instructor_id' => $import->user_id, 'status' => 'draft', 'level' => 'beginner', 'pricing_type' => 'free', 'currency' => 'GBP'];
            $course = Course::create($attrs + ['slug' => $slug]);
        } elseif ($attrs) {
            $course->update($attrs);
        }

        if ($course->pricing_type->value === 'paid' && $course->price_cents <= 0) {
            throw new \InvalidArgumentException("Paid course '$slug' needs a price greater than 0");
        }

        return $course;
    }

    private function storeMedia(string $name, string $dir): string
    {
        $ext = strtolower(pathinfo($name, PATHINFO_EXTENSION));
        if (! in_array($ext, self::ALLOWED_MEDIA, true)) {
            throw new \InvalidArgumentException("File type .$ext is not allowed ($name)");
        }

        $entry = $this->media[strtolower(basename($name))] ?? null;
        if (! $this->zip || ! $entry) {
            throw new \InvalidArgumentException("File '$name' was not found in the media ZIP");
        }

        $contents = $this->zip->getFromName($entry);
        if ($contents === false) {
            throw new \InvalidArgumentException("File '$name' could not be read from the ZIP");
        }

        $path = $dir.'/'.Str::uuid().'-'.Str::slug(pathinfo($name, PATHINFO_FILENAME)).'.'.$ext;
        Storage::disk('public')->put($path, $contents);

        return $path;
    }
}
