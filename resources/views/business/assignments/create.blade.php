<x-panel-layout title="Assign training">
<div class="mx-auto max-w-3xl px-4 py-8">
    <h1 class="display text-3xl">Assign training</h1>
    <p class="muted mt-1">Pick a course, who it's for, and when it's due.</p>

    <form method="POST" action="{{ route('business.assignments.store') }}" class="panel mt-6 space-y-6 p-6" x-data="{ mode: 'none' }">
        @csrf

        <div>
            <label class="text-sm font-medium">Course</label>
            <select name="course_id" class="field mt-1" required>
                <option value="">Select a course…</option>
                @foreach($courses as $c)<option value="{{ $c->id }}" @selected(old('course_id') == $c->id)>{{ $c->title }}</option>@endforeach
            </select>
            @error('course_id')<p class="mt-1 text-sm text-[var(--red,#e34948)]">{{ $message }}</p>@enderror
        </div>

        <div>
            <label class="text-sm font-medium">Assign to</label>
            <div class="field mt-1 max-h-56 space-y-1 overflow-y-auto !h-auto !py-2">
                @forelse($students as $s)
                    <label class="flex items-center gap-2 rounded px-2 py-1 text-sm hover:bg-black/5">
                        <input type="checkbox" name="user_ids[]" value="{{ $s->id }}" @checked(collect(old('user_ids'))->contains($s->id))>
                        {{ $s->name }} <span class="muted">{{ $s->email }}</span>
                    </label>
                @empty
                    <p class="muted px-2 py-1 text-sm">No learners to assign yet.</p>
                @endforelse
            </div>
            @error('user_ids')<p class="mt-1 text-sm text-[var(--red,#e34948)]">{{ $message }}</p>@enderror
        </div>

        <div>
            <label class="text-sm font-medium">Deadline</label>
            <div class="mt-2 space-y-2">
                <label class="flex items-center gap-2 text-sm"><input type="radio" name="due_mode" value="none" x-model="mode" checked> No deadline</label>
                <label class="flex items-center gap-2 text-sm">
                    <input type="radio" name="due_mode" value="fixed" x-model="mode"> Fixed date
                    <input type="date" name="due_date" class="field !w-auto !py-1" x-show="mode === 'fixed'" value="{{ old('due_date') }}">
                </label>
                <label class="flex items-center gap-2 text-sm">
                    <input type="radio" name="due_mode" value="rolling" x-model="mode"> Days after assignment
                    <input type="number" name="due_days" min="1" class="field !w-24 !py-1" x-show="mode === 'rolling'" value="{{ old('due_days', 30) }}">
                </label>
            </div>
        </div>

        <div>
            <label class="text-sm font-medium">Recurring refresher</label>
            <select name="recurrence" class="field mt-1">
                <option value="">Not recurring</option>
                @foreach(\App\Models\TrainingAssignment::RECURRENCES as $val => $label)
                    <option value="{{ $val }}" @selected(old('recurrence') === $val)>{{ $label }}</option>
                @endforeach
            </select>
            <p class="muted mt-1 text-xs">Once completed, the next cycle is assigned automatically with a fresh deadline.</p>
        </div>

        <div class="flex gap-3">
            <button class="btn">Assign</button>
            <a href="{{ route('business.assignments.index') }}" class="btn btn-quiet">Cancel</a>
        </div>
    </form>
</div>
</x-panel-layout>
