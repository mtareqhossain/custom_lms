<?php
namespace App\Exports;

use App\Models\Course;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;

class CoursesExport implements FromCollection, WithHeadings, WithMapping
{
    public function collection()
    {
        return Course::with(['instructor', 'category'])->withCount('enrollments')->orderBy('title')->get();
    }

    public function headings(): array
    {
        return ['Title', 'Instructor', 'Category', 'Level', 'Status', 'Pricing', 'Price', 'Currency', 'Access days', 'Students enrolled'];
    }

    public function map($course): array
    {
        return [
            $course->title,
            $course->instructor?->name ?? '',
            $course->category?->name ?? '',
            ucfirst($course->level),
            ucfirst($course->status),
            $course->isFree() ? 'Free' : 'Paid',
            $course->isFree() ? 0 : $course->price_cents / 100,
            $course->currency,
            $course->access_days ?? 'Lifetime',
            $course->enrollments_count,
        ];
    }
}
