<x-panel-layout :title="$course->title">
<div class="mx-auto max-w-6xl px-4 py-8">
    <a href="{{ route('business.dashboard') }}" class="muted text-sm">&larr; Overview</a>
    <div class="mt-2 flex flex-wrap items-center justify-between gap-3">
        <h1 class="display text-3xl">{{ $course->title }}</h1>
        <a href="{{ route('business.certificates.export', $course) }}" class="btn btn-quiet">Export certificates (.zip)</a>
    </div>

    <div class="mt-6 grid grid-cols-2 gap-4 lg:grid-cols-5">
        <x-stat label="Enrolled" :value="$enrollments->count()" />
        <x-stat label="Completed" :value="$enrollments->whereNotNull('completed_at')->count()" />
        <x-stat label="Average progress" :value="(int) round($enrollments->avg('progress_percent') ?? 0).'%'" />
        <x-stat label="Not started" :value="$enrollments->where('progress_percent', 0)->count()" />
        <x-stat
            label="Seats used"
            :value="$seatLimit ? $enrollments->count().' / '.$seatLimit : $enrollments->count().' (unlimited)'"
            class="col-span-2 lg:col-span-1"
        />
    </div>

    @if($seatLimit && $enrollments->count() >= $seatLimit)
        <div class="panel mt-4 p-4 text-sm" style="border-color:#F59E0B; background:#FEF3C7; color:#92400E;">
            This course is at its seat limit ({{ $seatLimit }}). Remove a student or ask an admin to raise the limit before adding more.
        </div>
    @endif

    <div class="panel mt-6 overflow-x-auto">
        <table class="tbl">
            <thead><tr><th>Student</th><th>Progress</th><th>Status</th><th></th></tr></thead>
            <tbody>
                @foreach($enrollments as $e)
                    <tr>
                        <td><p class="font-medium">{{ $e->user->name }}</p><p class="muted text-xs">{{ $e->user->email }}</p></td>
                        <td>
                            <div class="flex items-center gap-3">
                                <div class="bar w-28"><span style="width: {{ (int) $e->progress_percent }}%"></span></div>
                                <span class="muted text-xs">{{ (int) $e->progress_percent }}%</span>
                            </div>
                        </td>
                        <td>
                            @if($e->completed_at)<span class="pill">Completed {{ $e->completed_at->format('j M Y') }}</span>
                            @elseif($e->expires_at && $e->expires_at->isPast())<span class="pill pill-warn">Expired</span>
                            @else<span class="pill pill-grey">In progress</span>@endif
                        </td>
                        <td class="text-right">
                            @if($e->certificate)<a href="{{ route('certificates.download', $e->certificate->code) }}" class="text-sm font-medium underline">Certificate</a>@endif
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>
</div>
</x-panel-layout>
