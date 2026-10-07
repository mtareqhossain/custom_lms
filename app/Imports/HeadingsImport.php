<?php
namespace App\Imports;

use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\ToCollection;
use Maatwebsite\Excel\Concerns\WithHeadingRow;

/**
 * Tells Laravel Excel to treat row 1 as headings, so rows come back keyed by
 * column name (name, email, course_slug...). Used with Excel::toCollection().
 */
class HeadingsImport implements ToCollection, WithHeadingRow
{
    public function collection(Collection $rows): void
    {
        // Intentionally empty: we only use toCollection()'s return value.
    }
}
