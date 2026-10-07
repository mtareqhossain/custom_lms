<x-lms-layout>
    <div class="max-w-2xl mx-auto py-10 px-4">
        <h1 class="text-2xl font-bold mb-1" style="color:#10222B;">{{ $attempt->quiz->title }} — Result</h1>

        @if ($attempt->status === 'pending_review')
            <div class="p-5 rounded-xl mt-4" style="background:#FEF3C7;">
                <p class="font-medium" style="color:#92400E;">Submitted — awaiting review</p>
                <p class="text-sm mt-1" style="color:#92400E;">
                    This quiz includes short-answer questions that your instructor needs to grade manually.
                    You'll be able to see your final score once that's done.
                </p>
            </div>
        @else
            <div class="p-5 rounded-xl mt-4" style="background:{{ $attempt->passed ? '#DCEFEC' : '#FEE2E2' }};">
                <p class="text-3xl font-bold" style="color:{{ $attempt->passed ? '#085858' : '#991B1B' }};">
                    {{ $attempt->score_percentage }}%
                </p>
                <p class="font-medium mt-1" style="color:{{ $attempt->passed ? '#085858' : '#991B1B' }};">
                    {{ $attempt->passed ? 'Passed' : 'Not passed — pass mark is ' . $attempt->quiz->pass_percentage . '%' }}
                </p>
            </div>

            @if (!$attempt->passed && $attempt->quiz->canAttempt($attempt->user))
                <form method="POST" action="{{ route('quiz.start', $attempt->quiz) }}" class="mt-4">
                    @csrf
                    <button type="submit" class="px-4 py-2 rounded-lg text-white font-medium" style="background:#0B6E6E;">
                        Try again
                    </button>
                </form>
            @endif
        @endif

        <div class="mt-8 space-y-4">
            @foreach ($attempt->answers as $answer)
                <div class="p-4 rounded-xl border" style="border-color:#D5DCE1;">
                    <p class="font-medium" style="color:#10222B;">{{ $answer->question->question_text }}</p>
                    @if ($answer->question->type === 'short_text')
                        <p class="text-sm mt-1" style="color:#5B6B75;">Your answer: {{ $answer->short_text_answer ?: '(blank)' }}</p>
                        @if ($answer->is_correct === null)
                            <p class="text-xs mt-1" style="color:#92400E;">Awaiting manual review</p>
                        @endif
                    @else
                        <p class="text-sm mt-1" style="color:#5B6B75;">Your answer: {{ $answer->selectedOption?->option_text ?? '(no answer)' }}</p>
                    @endif
                    @if ($answer->is_correct !== null)
                        <p class="text-xs mt-1 font-medium" style="color:{{ $answer->is_correct ? '#085858' : '#991B1B' }};">
                            {{ $answer->is_correct ? 'Correct' : 'Incorrect' }}
                        </p>
                    @endif
                </div>
            @endforeach
        </div>

        <div class="mt-8 pt-6 border-t" style="border-color:#D5DCE1;">
            @php
                $quiz = $attempt->quiz;
                $backLesson = $quiz->lesson; // null for a course-final quiz
            @endphp

            @if ($backLesson)
                <a href="{{ route('learn.show', [$quiz->course, $backLesson]) }}" class="font-medium" style="color:#0B6E6E;">
                    &larr; Back to "{{ $backLesson->title }}"
                </a>
            @else
                <a href="{{ route('learn.my-courses') }}" class="font-medium" style="color:#0B6E6E;">
                    &larr; Back to my courses
                </a>
            @endif
        </div>
    </div>
</x-lms-layout>
