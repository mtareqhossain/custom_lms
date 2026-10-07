<?php
namespace App\Exports;

use App\Models\Category;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;

class CategoriesExport implements FromCollection, WithHeadings, WithMapping
{
    public function collection()
    {
        return Category::withCount('courses')->orderBy('name')->get();
    }

    public function headings(): array
    {
        return ['Name', 'Slug', 'Courses'];
    }

    public function map($category): array
    {
        return [$category->name, $category->slug, $category->courses_count];
    }
}
