<x-panel-layout title="Instructor dashboard">
<div class="mx-auto max-w-6xl px-4 py-10">
    <div class="flex flex-wrap items-center justify-between gap-3">
        <h1 class="display text-3xl">Your courses</h1>
        <a href="/admin/courses/create" class="btn">New course</a>
    </div>

    <div class="mt-6 grid grid-cols-2 gap-4 lg:grid-cols-4">
        <x-stat label="Courses" :value="$courses->count()" />
        <x-stat label="Learners" :value="$learners" />
        <x-stat label="Average progress" :value="$avgProgress.'%'" />
        <x-stat label="Active this week" :value="$activeThisWeek" />
    </div>

    <div class="panel mt-8 overflow-x-auto">
        <table class="w-full text-left text-sm">
            <thead class="border-b border-[var(--line)] text-xs uppercase tracking-wide text-[var(--muted)]">
                <tr><th class="p-4">Course</th><th class="p-4">Learners</th><th class="p-4">Completed</th><th class="p-4">Average progress</th><th class="p-4"></th></tr>
            </thead>
            <tbody class="divide-y divide-[var(--line)]">
                @forelse($courses as $c)
                    <tr>
                        <td class="p-4">
                            <p class="font-medium">{{ $c->title }}</p>
                            <p class="muted text-xs">{{ ucfirst($c->status) }}</p>
                        </td>
                        <td class="p-4">{{ $c->enrollments_count }}</td>
                        <td class="p-4">{{ $c->completed_count }}</td>
                        <td class="p-4">
                            <div class="bar w-32"><span style="width: {{ (int) round($c->avg_progress ?? 0) }}%"></span></div>
                        </td>
                        <td class="p-4 text-right"><a href="{{ route('instructor.course', $c) }}" class="font-medium underline">View</a></td>
                    </tr>
                @empty
                    <tr><td class="muted p-8 text-center" colspan="5">No courses yet. Create one in the studio to get started.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>
</x-panel-layout>
