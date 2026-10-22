<x-panel-layout title="Edit employee">
<div class="mx-auto max-w-xl px-4 py-8">
    <h1 class="display text-3xl">{{ $student->name }}</h1>
    <p class="muted mt-1">{{ $student->email }}</p>

    <form method="POST" action="{{ route('business.people.update', $student) }}" class="panel mt-6 space-y-5 p-6">
        @csrf @method('PUT')
        <div>
            <label class="text-sm font-medium">Full name</label>
            <input name="name" value="{{ old('name', $student->name) }}" class="field mt-1" required>
            @error('name')<p class="mt-1 text-sm text-[var(--red,#e34948)]">{{ $message }}</p>@enderror
        </div>
        <div>
            <label class="text-sm font-medium">Staff ID</label>
            <input name="employee_ref" value="{{ old('employee_ref', $student->employee_ref) }}" class="field mt-1">
        </div>
        <div>
            <label class="text-sm font-medium">Manager</label>
            <select name="manager_id" class="field mt-1">
                <option value="">No manager assigned</option>
                @foreach($managers as $m)<option value="{{ $m->id }}" @selected(old('manager_id', $student->manager_id) == $m->id)>{{ $m->name }}</option>@endforeach
            </select>
            @error('manager_id')<p class="mt-1 text-sm text-[var(--red,#e34948)]">{{ $message }}</p>@enderror
        </div>
        <div class="flex gap-3">
            <button class="btn">Save</button>
            <a href="{{ route('business.people.show', $student) }}" class="btn btn-quiet">View history</a>
        </div>
    </form>
</div>
</x-panel-layout>
