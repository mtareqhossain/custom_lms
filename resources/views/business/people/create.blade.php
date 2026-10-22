<x-panel-layout title="Add employee">
<div class="mx-auto max-w-xl px-4 py-8">
    <h1 class="display text-3xl">Add employee</h1>
    <p class="muted mt-1">They'll get an email to set their own password.</p>

    <form method="POST" action="{{ route('business.people.store') }}" class="panel mt-6 space-y-5 p-6">
        @csrf
        <div>
            <label class="text-sm font-medium">Full name</label>
            <input name="name" value="{{ old('name') }}" class="field mt-1" required>
            @error('name')<p class="mt-1 text-sm text-[var(--red,#e34948)]">{{ $message }}</p>@enderror
        </div>
        <div>
            <label class="text-sm font-medium">Email</label>
            <input type="email" name="email" value="{{ old('email') }}" class="field mt-1" required>
            @error('email')<p class="mt-1 text-sm text-[var(--red,#e34948)]">{{ $message }}</p>@enderror
        </div>
        <div>
            <label class="text-sm font-medium">Staff ID <span class="muted">(optional)</span></label>
            <input name="employee_ref" value="{{ old('employee_ref') }}" class="field mt-1">
        </div>
        <div>
            <label class="text-sm font-medium">Manager <span class="muted">(optional)</span></label>
            <select name="manager_id" class="field mt-1">
                <option value="">No manager assigned</option>
                @foreach($managers as $m)<option value="{{ $m->id }}" @selected(old('manager_id') == $m->id)>{{ $m->name }}</option>@endforeach
            </select>
        </div>
        <div class="flex gap-3">
            <button class="btn">Add employee</button>
            <a href="{{ route('business.students') }}" class="btn btn-quiet">Cancel</a>
        </div>
    </form>
</div>
</x-panel-layout>
