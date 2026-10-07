<x-filament-panels::page>
    @php $attempt = $this->getSelectedAttempt(); @endphp

    @if (!$attempt)
        <div class="divide-y divide-gray-200 dark:divide-gray-700 rounded-xl border border-gray-200 dark:border-gray-700">
            @forelse ($this->getPendingAttempts() as $pending)
                <div class="p-4 flex items-center justify-between">
                    <div>
                        <div class="font-medium">{{ $pending->quiz->title }}</div>
                        <div class="text-sm text-gray-500">
                            {{ $pending->user->name }} &middot; {{ $pending->quiz->course->title }} &middot;
                            submitted {{ $pending->submitted_at?->diffForHumans() }}
                        </div>
                    </div>
                    <x-filament::button wire:click="select({{ $pending->id }})" size="sm">
                        Grade
                    </x-filament::button>
                </div>
            @empty
                <div class="p-6 text-sm text-gray-500">No quiz attempts are waiting on manual grading.</div>
            @endforelse
        </div>
    @else
        <div class="mb-4">
            <x-filament::button wire:click="$set('selectedAttemptId', null)" size="sm" color="gray">
                &larr; Back to list
            </x-filament::button>
        </div>

        <h3 class="text-base font-semibold mb-1">{{ $attempt->quiz->title }}</h3>
        <p class="text-sm text-gray-500 mb-4">Student: {{ $attempt->user->name }}</p>

        <div class="space-y-4">
            @foreach ($attempt->answers->where('question.type', 'short_text') as $answer)
                <div class="p-4 rounded-xl border border-gray-200 dark:border-gray-700">
                    <p class="font-medium mb-2">{{ $answer->question->question_text }}</p>
                    <p class="text-sm mb-3 p-3 rounded-lg bg-gray-50 dark:bg-gray-800">{{ $answer->short_text_answer ?: '(blank)' }}</p>

                    <div class="flex items-center gap-4">
                        <label class="flex items-center gap-2 text-sm">
                            <input type="checkbox" wire:model="grades.{{ $answer->id }}.is_correct">
                            Mark correct
                        </label>
                        <label class="flex items-center gap-2 text-sm">
                            Points awarded:
                            <input type="number" step="0.5" min="0" max="{{ $answer->question->points }}"
                                   wire:model="grades.{{ $answer->id }}.points_awarded"
                                   class="w-20 rounded-lg border px-2 py-1 text-sm">
                            / {{ $answer->question->points }}
                        </label>
                    </div>
                </div>
            @endforeach
        </div>

        <div class="mt-6">
            <x-filament::button wire:click="saveGrades">
                Save grades &amp; finalize score
            </x-filament::button>
        </div>
    @endif
</x-filament-panels::page>
