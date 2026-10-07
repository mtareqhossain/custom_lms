<?php
namespace App\Exports;

use App\Models\Enrollment;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;

/** Student progress, one row per enrollment. Optionally scoped to one organization. */
class EnrollmentsExport implements FromCollection, WithHeadings, WithMapping
{
    public function __construct(private readonly ?int $organizationId = null) {}

    public function collection()
    {
        return Enrollment::with(['user', 'course', 'organization'])
            ->when($this->organizationId, fn ($q) => $q->where('organization_id', $this->organizationId))
            ->orderByDesc('created_at')
            ->get();
    }

    public function headings(): array
    {
        return ['Student', 'Email', 'Organization', 'Course', 'Source', 'Progress %', 'Status', 'Enrolled', 'Completed', 'Access expires'];
    }

    public function map($enrollment): array
    {
        $status = $enrollment->completed_at ? 'Completed'
            : (! $enrollment->isActive() ? 'Expired'
            : ($enrollment->progress_percent > 0 ? 'In progress' : 'Not started'));

        return [
            $enrollment->user->name,
            $enrollment->user->email,
            $enrollment->organization?->name ?? '',
            $enrollment->course->title,
            ucfirst($enrollment->source->value),
            $enrollment->progress_percent,
            $status,
            $enrollment->starts_at?->format('Y-m-d'),
            $enrollment->completed_at?->format('Y-m-d') ?? '',
            $enrollment->expires_at?->format('Y-m-d') ?? 'Never',
        ];
    }
}
