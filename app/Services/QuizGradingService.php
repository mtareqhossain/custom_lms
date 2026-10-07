<?php

namespace App\Services;

use App\Enums\QuizAttemptStatus;
use App\Models\Enrollment;
use App\Models\Quiz;
use App\Models\QuizAttempt;
use App\Models\QuizAttemptAnswer;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use RuntimeException;

class QuizGradingService
{
    public function __construct(private readonly ProgressService $progressService)
    {
    }

    public function startAttempt(User $user, Quiz $quiz, ?Enrollment $enrollment = null): QuizAttempt
    {
        if (!$quiz->canAttempt($user)) {
            throw new RuntimeException('No attempts remaining for this quiz.');
        }

        return QuizAttempt::create([
            'quiz_id' => $quiz->id,
            'user_id' => $user->id,
            'enrollment_id' => $enrollment?->id,
            'attempt_number' => $quiz->attemptsUsedBy($user) + 1,
            'status' => QuizAttemptStatus::InProgress->value,
            'started_at' => now(),
        ]);
    }

    /**
     * @param array<int,array{selected_option_id?:int,short_text_answer?:string}> $answers keyed by question_id
     */
    public function submitAttempt(QuizAttempt $attempt, array $answers): QuizAttempt
    {
        $quiz = $attempt->quiz;
        $hasUngraded = false;

        DB::transaction(function () use ($attempt, $answers, &$hasUngraded) {
            foreach ($attempt->quiz->questions as $question) {
                $submitted = $answers[$question->id] ?? [];

                if ($question->isAutoGradable()) {
                    $selectedOptionId = $submitted['selected_option_id'] ?? null;
                    $correctOption = $question->correctOption();
                    $isCorrect = $selectedOptionId !== null && $correctOption && (int) $selectedOptionId === $correctOption->id;

                    QuizAttemptAnswer::create([
                        'quiz_attempt_id' => $attempt->id,
                        'quiz_question_id' => $question->id,
                        'selected_option_id' => $selectedOptionId,
                        'is_correct' => $isCorrect,
                        'points_awarded' => $isCorrect ? $question->points : 0,
                    ]);
                } else {
                    $hasUngraded = true;
                    QuizAttemptAnswer::create([
                        'quiz_attempt_id' => $attempt->id,
                        'quiz_question_id' => $question->id,
                        'short_text_answer' => $submitted['short_text_answer'] ?? null,
                        'is_correct' => null,
                        'points_awarded' => null,
                    ]);
                }
            }

            $attempt->update(['submitted_at' => now()]);
        });

        if ($hasUngraded) {
            $attempt->update(['status' => QuizAttemptStatus::PendingReview->value]);
            return $attempt->refresh();
        }

        return $this->finalize($attempt);
    }

    /**
     * Admin/instructor grades a short_text answer. Once every short_text
     * answer in the attempt has a grade, the attempt is finalized (score
     * computed, pass/fail decided, and — if it just passed — the student's
     * course completion/certificate is re-checked).
     */
    public function gradeAnswer(QuizAttemptAnswer $answer, bool $isCorrect, float $pointsAwarded): QuizAttempt
    {
        $answer->update(['is_correct' => $isCorrect, 'points_awarded' => $pointsAwarded]);

        $attempt = $answer->attempt;
        $stillUngraded = $attempt->answers()->whereNull('points_awarded')->exists();

        if ($stillUngraded) {
            return $attempt->refresh();
        }

        return $this->finalize($attempt, gradedBy: auth()->id());
    }

    private function finalize(QuizAttempt $attempt, ?int $gradedBy = null): QuizAttempt
    {
        $quiz = $attempt->quiz;
        $totalPoints = $quiz->totalPoints();
        $earnedPoints = (float) $attempt->answers()->sum('points_awarded');

        $scorePercentage = $totalPoints > 0 ? round(($earnedPoints / $totalPoints) * 100, 2) : 0;
        $passed = $scorePercentage >= $quiz->pass_percentage;

        $attempt->update([
            'status' => QuizAttemptStatus::Graded->value,
            'score_percentage' => $scorePercentage,
            'passed' => $passed,
            'graded_at' => now(),
            'graded_by' => $gradedBy,
        ]);

        // A required quiz being passed can be the missing piece that completes
        // the course (lessons may already be at 100%) — re-check now.
        if ($passed && $attempt->enrollment) {
            $this->progressService->recalculate($attempt->enrollment);
        }

        return $attempt->refresh();
    }
}
