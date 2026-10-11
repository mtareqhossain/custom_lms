<?php
namespace App\Jobs;

use App\Imports\HeadingsImport;
use App\Models\BulkImport;
use App\Models\Course;
use App\Models\Lesson;
use App\Models\Section;
use App\Services\ContentIndexer;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Maatwebsite\Excel\Facades\Excel;
use ZipArchive;

/**
 * Bulk-imports lessons into ONE existing course (unlike Phase 5's ImportContentJob, which can
 * also create whole new courses across a spreadsheet). One row = one lesson.
 */
class ImportLessonsJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, SerializesModels;

    public int $tries = 1;
    public int $timeout = 600;

    private const ALLOWED_MEDIA = ['pdf', 'doc', 'docx', 'ppt', 'pptx', 'xls', 'xlsx', 'txt', 'jpg', 'jpeg', 'png'];
    private ?ZipArchive $zip = null;
    private array $media = [];

    public function __construct(
        private readonly int $bulkImportId,
        private readonly int $courseId,
        private readonly ?string $mediaZipPath = null,
    ) {}

    public function handle(): void
    {
        $import = BulkImport::findOrFail($this->bulkImportId);
        $course = Course::findOrFail($this->courseId);
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
                DB::transaction(fn () => $this->importRow($row, $course));
                $ok++;
            } catch (\Throwable $e) {
                $failed++;
                if (count($errors) < 100) {
                    $errors[] = ['row' => $i + 2, 'error' => $e->getMessage()];
                }
            }
        }

        $this->zip?->close();

        // Re-index this course now that its lessons changed, so the course
        // chatbot can see the newly imported content.
        if ($ok > 0) {
            app(ContentIndexer::class)->indexCourse($course);
        }

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

    private function importRow($row, Course $course): void
    {
        $get = function (string $key) use ($row): ?string {
            $v = trim((string) ($row[$key] ?? ''));
            return $v === '' ? null : $v;
        };

        $sectionTitle = $get('section');
        $lessonTitle = $get('lesson_title');
        if (! $sectionTitle || ! $lessonTitle) {
            throw new \InvalidArgumentException('section and lesson_title are required');
        }

        $type = $get('lesson_type') ? strtolower($get('lesson_type')) : ($get('video_ref') ? 'video' : 'text');
        if (! in_array($type, ['video', 'text', 'file'], true)) {
            throw new \InvalidArgumentException('lesson_type must be video, text, or file');
        }
        if ($type === 'video' && (! $get('video_provider') || ! $get('video_ref'))) {
            throw new \InvalidArgumentException('Video lessons need video_provider and video_ref');
        }
        if ($get('video_provider') && ! in_array(strtolower($get('video_provider')), ['youtube', 'vimeo', 'bunny', 's3'], true)) {
            throw new \InvalidArgumentException('video_provider must be youtube, vimeo, bunny, or s3');
        }

        $section = Section::whereRaw('LOWER(title) = ?', [strtolower($sectionTitle)])
            ->where('course_id', $course->id)->first()
            ?? Section::create([
                'course_id' => $course->id,
                'title' => $sectionTitle,
                'position' => (int) Section::where('course_id', $course->id)->max('position') + 1,
            ]);

        $lesson = Lesson::whereRaw('LOWER(title) = ?', [strtolower($lessonTitle)])
            ->where('section_id', $section->id)->first() ?? new Lesson(['section_id' => $section->id, 'title' => $lessonTitle]);

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
            $lesson->attachment = $this->storeMedia($get('attachment_file'));
        }

        $lesson->save();
    }

    private function storeMedia(string $name): string
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
        $path = 'attachments/'.\Illuminate\Support\Str::uuid().'-'.\Illuminate\Support\Str::slug(pathinfo($name, PATHINFO_FILENAME)).'.'.$ext;
        Storage::disk('public')->put($path, $contents);

        return $path;
    }
}
