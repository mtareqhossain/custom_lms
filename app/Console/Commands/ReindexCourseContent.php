<?php

namespace App\Console\Commands;

use App\Models\Course;
use App\Services\ContentIndexer;
use Illuminate\Console\Command;

class ReindexCourseContent extends Command
{
    protected $signature = 'courses:reindex-content {course_id?}';

    protected $description = 'Chunk and embed lesson content so the student chatbot can search it. Run once after installing Phase 18, and again any time you bulk-import lessons outside the admin UI.';

    public function handle(ContentIndexer $indexer): int
    {
        $courseId = $this->argument('course_id');

        $courses = $courseId
            ? Course::where('id', $courseId)->get()
            : Course::all();

        if ($courses->isEmpty()) {
            $this->error('No matching course(s) found.');
            return self::FAILURE;
        }

        $bar = $this->output->createProgressBar($courses->count());
        foreach ($courses as $course) {
            $indexer->indexCourse($course);
            $bar->advance();
        }
        $bar->finish();
        $this->newLine();
        $this->info('Done. Lesson content is now searchable by the chatbot.');

        return self::SUCCESS;
    }
}
