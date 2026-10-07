<?php

namespace App\Filament\Pages;

use App\Models\QuizAttempt;
use App\Services\QuizGradingService;
use Filament\Notifications\Notification;
use Filament\Pages\Page;

/**
 * Lists quiz attempts stuck in "pending_review" (they have short_text
 * answers that can't be auto-graded) and lets admin/instructor grade each
 * answer. Scoped to instructor-owned courses the same way QuizResource is.
 */
class QuizGradingPage extends Page
{
    protected static ?string $navigationIcon = 'heroicon-o-pencil-square';

    protected static ?string $navigationGroup = 'Content';

    protected static ?string $navigationLabel = 'Quiz Grading';

    protected static ?int $navigationSort = 4;

    protected static string $view = 'filament.pages.quiz-grading-page';

    public ?int $selectedAttemptId = null;

    /** @var array<int,array{is_correct:bool,points_awarded:float}> keyed by answer id */
    public array $grades = [];

    public function getPendingAttempts()
    {
        $user = auth()->user();

        $query = QuizAttempt::where('status', 'pending_review')
            ->with(['quiz.course', 'user']);

        if ($user->hasRole('instructor') && !$user->hasRole('admin')) {
            $query->whereHas('quiz.course', fn ($q) => $q->where('instructor_id', $user->id));
        }

        return $query->latest('submitted_at')->get();
    }

    public function select(int $attemptId): void
    {
        $this->selectedAttemptId = $attemptId;
        $this->grades = [];
    }

    public function getSelectedAttempt(): ?QuizAttempt
    {
        if (!$this->selectedAttemptId) {
            return null;
        }

        return QuizAttempt::with(['quiz', 'answers.question'])->find($this->selectedAttemptId);
    }

    public function saveGrades(QuizGradingService $grading): void
    {
        $attempt = $this->getSelectedAttempt();
        if (!$attempt) {
            return;
        }

        foreach ($attempt->answers as $answer) {
            if ($answer->question->type !== 'short_text') {
                continue;
            }

            $grade = $this->grades[$answer->id] ?? null;
            if ($grade === null) {
                continue; // not graded yet this round — leave as-is
            }

            $grading->gradeAnswer(
                $answer,
                (bool) ($grade['is_correct'] ?? false),
                (float) ($grade['points_awarded'] ?? 0)
            );
        }

        Notification::make()->title('Grades saved')->success()->send();
        $this->selectedAttemptId = null;
        $this->grades = [];
    }
}
