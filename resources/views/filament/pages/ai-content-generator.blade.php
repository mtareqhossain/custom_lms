<x-filament-panels::page>
    <form wire:submit="generate">
        {{ $this->form }}

        <div class="mt-6">
            <x-filament::button type="submit">
                Generate draft
            </x-filament::button>
        </div>
    </form>

    @if ($generatedHtml)
        <div class="mt-8 p-4 rounded-xl border border-gray-200 dark:border-gray-700">
            <h3 class="text-base font-semibold mb-3">Draft (review before applying)</h3>
            <div class="prose dark:prose-invert max-w-none mb-4">
                {!! $generatedHtml !!}
            </div>
            <div class="flex gap-3">
                <x-filament::button wire:click="apply" color="success">
                    Apply to selected course/lesson
                </x-filament::button>
                <x-filament::button wire:click="$set('generatedHtml', null)" color="gray">
                    Discard
                </x-filament::button>
            </div>
        </div>
    @endif

    @if ($generatedQuizQuestions)
        <div class="mt-8 p-4 rounded-xl border border-gray-200 dark:border-gray-700">
            <h3 class="text-base font-semibold mb-3">Draft quiz — {{ count($generatedQuizQuestions) }} question(s) (review before creating)</h3>

            <div class="space-y-4 mb-4">
                @foreach ($generatedQuizQuestions as $i => $q)
                    <div class="p-3 rounded-lg bg-gray-50 dark:bg-gray-800">
                        <p class="font-medium mb-2">{{ $i + 1 }}. {{ $q['question'] }}</p>
                        <ul class="text-sm space-y-1">
                            @foreach ($q['options'] as $optIndex => $option)
                                <li class="{{ $optIndex === (int) $q['correct_index'] ? 'font-semibold text-green-700 dark:text-green-400' : '' }}">
                                    {{ chr(65 + $optIndex) }}. {{ $option }}
                                    @if ($optIndex === (int) $q['correct_index'])
                                        <span class="text-xs">(correct)</span>
                                    @endif
                                </li>
                            @endforeach
                        </ul>
                    </div>
                @endforeach
            </div>

            <div class="flex gap-3">
                <x-filament::button wire:click="apply" color="success">
                    Create this quiz
                </x-filament::button>
                <x-filament::button wire:click="$set('generatedQuizQuestions', null)" color="gray">
                    Discard
                </x-filament::button>
            </div>
        </div>
    @endif
</x-filament-panels::page>
