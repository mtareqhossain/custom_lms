<?php

namespace App\Http\Controllers;

use App\Models\Enrollment;
use App\Models\Quiz;
use App\Models\QuizAttempt;
use App\Services\QuizGradingService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class QuizController extends Controller
{
    /**
     * Show the quiz: either a fresh start screen (attempts remaining, pass
     * mark, time limit) or — if there's an attempt in progress — the question
     * form itself.
     */
    public function show(Quiz $quiz)
    {
        $user = Auth::user();
        $enrollment = $this->enrollmentFor($quiz, $user);

        $inProgress = QuizAttempt::where('quiz_id', $quiz->id)
            ->where('user_id', $user->id)
            ->where('status', 'in_progress')
            ->latest()
            ->first();

        return view('quiz.show', [
            'quiz' => $quiz->load('questions.options'),
            'attempt' => $inProgress,
            'bestAttempt' => $quiz->bestAttempt($user),
            'attemptsRemaining' => $quiz->attemptsRemaining($user),
            'hasPassed' => $quiz->hasPassed($user),
            'enrollment' => $enrollment,
        ]);
    }

    public function start(Quiz $quiz, QuizGradingService $grading)
    {
        $user = Auth::user();
        $enrollment = $this->enrollmentFor($quiz, $user);

        try {
            $attempt = $grading->startAttempt($user, $quiz, $enrollment);
        } catch (\RuntimeException $e) {
            return back()->withErrors(['quiz' => $e->getMessage()]);
        }

        return redirect()->route('quiz.show', $quiz)->with('attempt_id', $attempt->id);
    }

    public function submit(Request $request, Quiz $quiz, QuizGradingService $grading)
    {
        $user = Auth::user();

        $attempt = QuizAttempt::where('quiz_id', $quiz->id)
            ->where('user_id', $user->id)
            ->where('status', 'in_progress')
            ->latest()
            ->first();

        if (!$attempt) {
            return redirect()->route('quiz.show', $quiz)->withErrors(['quiz' => 'No active attempt found — please start the quiz again.']);
        }

        // $request->input('answers') is keyed by question_id:
        // { "12": {"selected_option_id": 45}, "13": {"short_text_answer": "..."} }
        $answers = $request->input('answers', []);

        $attempt = $grading->submitAttempt($attempt, $answers);

        return redirect()->route('quiz.result', $attempt);
    }

    public function result(QuizAttempt $attempt)
    {
        abort_unless($attempt->user_id === Auth::id(), 403);

        return view('quiz.result', [
            'attempt' => $attempt->load('quiz', 'answers.question.options'),
        ]);
    }

    private function enrollmentFor(Quiz $quiz, $user): ?Enrollment
    {
        return Enrollment::where('user_id', $user->id)
            ->where('course_id', $quiz->course_id)
            ->first();
    }
}
