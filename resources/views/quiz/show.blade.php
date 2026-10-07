<x-lms-layout>
    <div class="max-w-2xl mx-auto py-10 px-4">
        <div class="mb-4">
            @if ($quiz->lesson)
                <a href="{{ route('learn.show', [$quiz->course, $quiz->lesson]) }}" class="text-sm font-medium" style="color:#0B6E6E;">
                    &larr; Back to "{{ $quiz->lesson->title }}"
                </a>
            @else
                <a href="{{ route('learn.my-courses') }}" class="text-sm font-medium" style="color:#0B6E6E;">
                    &larr; Back to my courses
                </a>
            @endif
        </div>

        <h1 class="text-2xl font-bold mb-1" style="color:#10222B;">{{ $quiz->title }}</h1>
        <p class="text-sm mb-6" style="color:#5B6B75;">{{ $quiz->description }}</p>

        @if ($errors->any())
            <div class="mb-4 p-3 rounded-lg text-sm" style="background:#FEE2E2; color:#991B1B;">
                {{ $errors->first() }}
            </div>
        @endif

        @if ($hasPassed)
            <div class="mb-6 p-4 rounded-xl" style="background:#DCEFEC;">
                <p class="font-medium" style="color:#085858;">You've already passed this quiz.</p>
                @if ($bestAttempt)
                    <p class="text-sm" style="color:#5B6B75;">Best score: {{ $bestAttempt->score_percentage }}%</p>
                @endif
            </div>
        @endif

        @if (!$attempt)
            {{-- Start screen --}}
            <div class="p-5 rounded-xl border" style="border-color:#D5DCE1;">
                <ul class="text-sm space-y-1 mb-4" style="color:#5B6B75;">
                    <li>{{ $quiz->questions->count() }} question(s)</li>
                    <li>Pass mark: {{ $quiz->pass_percentage }}%</li>
                    <li>
                        @if ($attemptsRemaining === null)
                            Unlimited attempts
                        @else
                            {{ $attemptsRemaining }} attempt(s) remaining
                        @endif
                    </li>
                    @if ($quiz->time_limit_minutes)
                        <li>Time limit: {{ $quiz->time_limit_minutes }} minutes</li>
                    @endif
                    @if ($quiz->is_required)
                        <li class="font-medium" style="color:#085858;">This quiz is required to complete the course.</li>
                    @endif
                </ul>

                @if ($attemptsRemaining === 0)
                    <p class="text-sm font-medium" style="color:#991B1B;">You've used all your attempts for this quiz.</p>
                @else
                    <form method="POST" action="{{ route('quiz.start', $quiz) }}">
                        @csrf
                        <button type="submit" class="px-4 py-2 rounded-lg text-white font-medium" style="background:#0B6E6E;">
                            {{ $hasPassed ? 'Retake quiz' : 'Start quiz' }}
                        </button>
                    </form>
                @endif
            </div>
        @else
            {{-- Question form --}}
            <form method="POST" action="{{ route('quiz.submit', $quiz) }}" class="space-y-6">
                @csrf
                @foreach ($quiz->questions as $i => $question)
                    <div class="p-4 rounded-xl border" style="border-color:#D5DCE1;">
                        <p class="font-medium mb-3" style="color:#10222B;">{{ $i + 1 }}. {{ $question->question_text }}</p>

                        @if ($question->type === 'short_text')
                            <textarea name="answers[{{ $question->id }}][short_text_answer]" rows="3"
                                      class="w-full rounded-lg border px-3 py-2 text-sm" style="border-color:#D5DCE1;"
                                      placeholder="Type your answer..."></textarea>
                            <p class="text-xs mt-1" style="color:#5B6B75;">This answer will be reviewed manually by your instructor.</p>
                        @else
                            <div class="space-y-2">
                                @foreach ($question->options as $option)
                                    <label class="flex items-center gap-2 text-sm" style="color:#10222B;">
                                        <input type="radio" name="answers[{{ $question->id }}][selected_option_id]" value="{{ $option->id }}" required>
                                        {{ $option->option_text }}
                                    </label>
                                @endforeach
                            </div>
                        @endif
                    </div>
                @endforeach

                <button type="submit" class="px-5 py-2.5 rounded-lg text-white font-medium" style="background:#0B6E6E;">
                    Submit quiz
                </button>
            </form>
        @endif
    </div>
</x-lms-layout>
