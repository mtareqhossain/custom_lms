@props(['quiz', 'user'])

@php
    $hasPassed = $quiz->hasPassed($user);
    $remaining = $quiz->attemptsRemaining($user);
@endphp

<div class="p-4 rounded-xl border flex items-center justify-between" style="border-color:#D5DCE1; background:#fff;">
    <div>
        <p class="font-medium flex items-center gap-2" style="color:#10222B;">
            {{ $quiz->title }}
            @if ($quiz->is_required)
                <span class="text-xs px-2 py-0.5 rounded-full" style="background:#F0B23A; color:#10222B;">Required</span>
            @endif
        </p>
        <p class="text-xs mt-1" style="color:#5B6B75;">
            Pass mark {{ $quiz->pass_percentage }}%
            @if ($hasPassed)
                &middot; <span style="color:#085858;">Passed</span>
            @elseif ($remaining === 0)
                &middot; <span style="color:#991B1B;">No attempts remaining</span>
            @endif
        </p>
    </div>
    <a href="{{ route('quiz.show', $quiz) }}" class="px-3 py-1.5 rounded-lg text-sm font-medium text-white" style="background:#0B6E6E;">
        {{ $hasPassed ? 'Review' : 'Take quiz' }}
    </a>
</div>
