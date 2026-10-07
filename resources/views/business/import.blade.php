<x-panel-layout title="Import students">
<div class="mx-auto max-w-2xl px-4 py-10">
    <a href="{{ route('business.dashboard') }}" class="muted text-sm">&larr; Back to dashboard</a>
    <h1 class="display mt-2 text-3xl">Import students</h1>
    <p class="muted mt-2">Upload a spreadsheet with the columns <code>name</code>, <code>email</code> and optionally <code>employee_ref</code>.
        <a class="underline" href="{{ asset('templates/student-import-template.csv') }}" download>Download the template</a>.</p>

    @if($courses->isEmpty())
        <div class="panel mt-6 p-6 text-center">
            <p class="font-medium">No courses are assigned to your organization yet.</p>
            <p class="muted mt-1 text-sm">Contact us and we'll set up your courses.</p>
        </div>
    @else
        <form method="POST" action="{{ route('business.import.store') }}" enctype="multipart/form-data" class="panel mt-6 space-y-4 p-6">
            @csrf
            <div>
                <label class="text-sm font-medium" for="course_id">Course to enroll them in</label>
                <select id="course_id" name="course_id" required class="field mt-1 w-full">
                    @foreach($courses as $c)<option value="{{ $c->id }}">{{ $c->title }}</option>@endforeach
                </select>
                @error('course_id')<p class="mt-1 text-sm text-red-600">{{ $message }}</p>@enderror
            </div>
            <div>
                <label class="text-sm font-medium" for="file">Spreadsheet</label>
                <input id="file" type="file" name="file" required accept=".xlsx,.xls,.csv" class="field mt-1 w-full">
                @error('file')<p class="mt-1 text-sm text-red-600">{{ $message }}</p>@enderror
            </div>
            <button class="btn">Start import</button>
        </form>
    @endif

    @if($recent->isNotEmpty())
        <h2 class="display mt-10 text-xl">Recent imports</h2>
        <div class="panel mt-3 divide-y divide-[var(--line)]">
            @foreach($recent as $import)
                <div class="p-4 text-sm">
                    <div class="flex flex-wrap items-center justify-between gap-3">
                        <span>{{ $import->created_at->format('j M, g:ia') }}</span>
                        <span class="pill">{{ ucfirst($import->status) }}</span>
                        <span class="muted">{{ $import->processed_rows }} added, {{ $import->failed_rows }} failed</span>
                    </div>
                    @if(!empty($import->errors))
                        <ul class="mt-2 list-disc pl-5 text-red-600">
                            @foreach(array_slice($import->errors, 0, 5) as $err)
                                <li>Row {{ $err['row'] }}: {{ $err['error'] }}</li>
                            @endforeach
                        </ul>
                    @endif
                </div>
            @endforeach
        </div>
    @endif
</div>
</x-panel-layout>
