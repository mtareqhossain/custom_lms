<?php

namespace App\Jobs;

use App\Enums\QuizQuestionType;
use App\Imports\HeadingsImport;
use App\Models\Course;
use App\Models\Lesson;
use App\Models\Quiz;
use App\Models\QuizImportBatch;
use App\Models\QuizQuestion;
use App\Models\QuizQuestionOption;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Storage;
use Maatwebsite\Excel\Facades\Excel;

/**
 * One row = one question. Rows sharing the same (quiz_title, course_title,
 * lesson_title) are grouped into the same Quiz — the quiz-level columns
 * (is_required, pass_percentage, max_attempts, time_limit_minutes) are read
 * from the FIRST row of each group; later rows only need the question columns.
 *
 * Expected columns (matches QuizzesExport's output, so export -> edit -> import round-trips):
 *   quiz_title, course_title, lesson_title, is_required, pass_percentage,
 *   max_attempts, time_limit_minutes, question_type, question_text, points,
 *   option_1, option_1_correct, option_2, option_2_correct,
 *   option_3, option_3_correct, option_4, option_4_correct
 */
class ImportQuizzesJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, SerializesModels;

    public int $tries = 1;
    public int $timeout = 600;

    public function __construct(private readonly int $batchId)
    {
    }

    public function handle(): void
    {
        $batch = QuizImportBatch::findOrFail($this->batchId);
        $batch->update(['status' => 'processing']);

        try {
            $rows = Excel::toCollection(new HeadingsImport, Storage::disk('local')->path($batch->file_path))->first() ?? collect();
        } catch (\Throwable $e) {
            $batch->update(['status' => 'failed', 'errors' => [['row' => 0, 'error' => 'Could not read the file: ' . $e->getMessage()]]]);
            return;
        }

        $errors = [];
        $quizzesCreated = 0;
        $questionsCreated = 0;
        $quizCache = []; // key => Quiz
        $orderCounters = []; // quiz_id => next order_index

        foreach ($rows as $i => $row) {
            $rowNum = $i + 2;

            if ($row->filter(fn ($v) => $v !== null && $v !== '')->isEmpty()) {
                continue;
            }

            $quizTitle = trim((string) ($row['quiz_title'] ?? ''));
            $courseTitle = trim((string) ($row['course_title'] ?? ''));
            $lessonTitle = trim((string) ($row['lesson_title'] ?? ''));
            $questionType = trim((string) ($row['question_type'] ?? ''));
            $questionText = trim((string) ($row['question_text'] ?? ''));

            if ($quizTitle === '' || $courseTitle === '' || $questionText === '') {
                $errors[] = ['row' => $rowNum, 'error' => 'Missing quiz_title, course_title, or question_text'];
                continue;
            }

            if (!in_array($questionType, array_map(fn ($c) => $c->value, QuizQuestionType::cases()), true)) {
                $errors[] = ['row' => $rowNum, 'error' => "Invalid question_type [{$questionType}] — must be multiple_choice, true_false, or short_text"];
                continue;
            }

            $course = Course::where('title', $courseTitle)->first();
            if (!$course) {
                $errors[] = ['row' => $rowNum, 'error' => "Course not found: {$courseTitle}"];
                continue;
            }

            $lesson = null;
            if ($lessonTitle !== '') {
                $lesson = Lesson::whereHas('section', fn ($q) => $q->where('course_id', $course->id))
                    ->where('title', $lessonTitle)
                    ->first();
                if (!$lesson) {
                    $errors[] = ['row' => $rowNum, 'error' => "Lesson not found in course: {$lessonTitle}"];
                    continue;
                }
            }

            $cacheKey = $quizTitle . '|' . $course->id . '|' . ($lesson?->id ?? 'final');

            if (!isset($quizCache[$cacheKey])) {
                $quiz = Quiz::updateOrCreate(
                    ['title' => $quizTitle, 'course_id' => $course->id, 'lesson_id' => $lesson?->id],
                    [
                        'description' => null,
                        'is_required' => $this->parseBool($row['is_required'] ?? false),
                        'pass_percentage' => (int) ($row['pass_percentage'] ?? 70),
                        'max_attempts' => $this->parseNullableInt($row['max_attempts'] ?? null),
                        'time_limit_minutes' => $this->parseNullableInt($row['time_limit_minutes'] ?? null),
                        'is_active' => true,
                    ]
                );
                // Fresh import: clear previously imported questions for this quiz
                // so re-importing an edited export doesn't duplicate questions.
                $quiz->questions()->delete();
                $quizCache[$cacheKey] = $quiz;
                $orderCounters[$quiz->id] = 0;
                $quizzesCreated++;
            }

            $quiz = $quizCache[$cacheKey];

            $question = QuizQuestion::create([
                'quiz_id' => $quiz->id,
                'type' => $questionType,
                'question_text' => $questionText,
                'points' => (int) ($row['points'] ?? 1),
                'order_index' => $orderCounters[$quiz->id]++,
            ]);

            if ($questionType !== QuizQuestionType::ShortText->value) {
                foreach ([1, 2, 3, 4] as $n) {
                    $optionText = trim((string) ($row["option_{$n}"] ?? ''));
                    if ($optionText === '') {
                        continue;
                    }
                    QuizQuestionOption::create([
                        'quiz_question_id' => $question->id,
                        'option_text' => $optionText,
                        'is_correct' => $this->parseBool($row["option_{$n}_correct"] ?? false),
                        'order_index' => $n,
                    ]);
                }
            }

            $questionsCreated++;
        }

        $batch->update([
            'status' => 'completed',
            'quizzes_created' => $quizzesCreated,
            'questions_created' => $questionsCreated,
            'failed_rows' => count($errors),
            'errors' => array_slice($errors, 0, 100),
        ]);
    }

    private function parseBool($value): bool
    {
        return in_array(mb_strtolower(trim((string) $value)), ['yes', 'true', '1', 'y'], true);
    }

    private function parseNullableInt($value): ?int
    {
        $value = trim((string) $value);
        return $value === '' ? null : (int) $value;
    }
}
