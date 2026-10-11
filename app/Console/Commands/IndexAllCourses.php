<?php

namespace App\Console\Commands;

use App\Models\Course;
use App\Services\ContentIndexer;
use Illuminate\Console\Command;

class IndexAllCourses extends Command
{
    protected $signature = 'courses:index-all';
    protected $description = 'Index every course\'s lesson content for the course chatbot (one-off backfill)';

    public function handle(ContentIndexer $indexer): int
    {
        $courses = Course::all();
        $this->withProgressBar($courses, function (Course $course) use ($indexer) {
            $indexer->indexCourse($course);
        });
        $this->newLine(2);
        $this->info("Indexed {$courses->count()} course(s).");

        return self::SUCCESS;
    }
}
