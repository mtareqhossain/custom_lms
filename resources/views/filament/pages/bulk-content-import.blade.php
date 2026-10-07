<x-filament-panels::page>
    <x-filament::section heading="Upload" description="One row per lesson. Re-running the same file is safe: existing courses, sections and lessons are updated, not duplicated.">
        <form wire:submit="submit" class="space-y-6">
            {{ $this->form }}
            <x-filament::button type="submit">Start import</x-filament::button>
        </form>
        <p class="mt-4 text-sm text-gray-500">
            Need the column layout?
            <a class="underline" href="{{ asset('templates/course-import-template.csv') }}" download>Download the template</a>.
            Videos are referenced by ID or path (YouTube, Vimeo, Bunny, S3) and are not uploaded through the ZIP.
        </p>
    </x-filament::section>

    <x-filament::section heading="Recent imports">
        <div class="space-y-3 text-sm">
            @forelse($this->recentImports() as $import)
                <div class="rounded-lg border border-gray-200 p-3 dark:border-white/10">
                    <div class="flex flex-wrap items-center justify-between gap-2">
                        <span>{{ $import->created_at->format('j M Y, g:ia') }}</span>
                        <span class="font-medium">{{ ucfirst($import->status) }}</span>
                        <span>{{ $import->processed_rows }} rows imported, {{ $import->failed_rows }} failed</span>
                    </div>
                    @if(!empty($import->errors))
                        <ul class="mt-2 list-disc pl-5 text-red-600">
                            @foreach(array_slice($import->errors, 0, 8) as $err)
                                <li>Row {{ $err['row'] }}: {{ $err['error'] }}</li>
                            @endforeach
                        </ul>
                    @endif
                </div>
            @empty
                <p class="text-gray-500">No imports yet.</p>
            @endforelse
        </div>
    </x-filament::section>
</x-filament-panels::page>
