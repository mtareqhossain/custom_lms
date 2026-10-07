<x-panel-layout title="Certificates">
<div class="mx-auto max-w-6xl px-4 py-8">
    <h1 class="display text-3xl">Certificates</h1>
    <p class="muted mt-1">Everyone in your organization who has completed a course. To download a whole course at once, open it from the overview and use Export.</p>

    <div class="panel mt-6 overflow-x-auto">
        <table class="tbl">
            <thead><tr><th>Student</th><th>Course</th><th>Issued</th><th>Code</th><th></th></tr></thead>
            <tbody>
                @forelse($certificates as $c)
                    <tr>
                        <td>
                            <p class="font-medium">{{ $c->enrollment->user->name }}</p>
                            <p class="muted text-xs">{{ $c->enrollment->user->email }}</p>
                        </td>
                        <td>{{ $c->enrollment->course->title }}</td>
                        <td>{{ $c->issued_at->format('j M Y') }}</td>
                        <td class="muted">{{ Str::substr($c->code, 0, 8) }}</td>
                        <td class="space-x-3 text-right">
                            <a href="{{ route('certificates.download', $c->code) }}" class="text-sm font-medium underline">PDF</a>
                            <a href="{{ route('certificates.show', $c->code) }}" class="muted text-sm underline">Verify</a>
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="5" class="muted !py-10 text-center">No certificates issued yet.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
    <div class="mt-6">{{ $certificates->links() }}</div>
</div>
</x-panel-layout>
