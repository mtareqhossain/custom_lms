<?php
namespace App\Imports;

/** One validated spreadsheet row, keyed by lowercase header name (name, email, employee_ref). */
class StudentRow
{
    public function __construct(
        public readonly int $rowNumber,
        public readonly string $name,
        public readonly string $email,
        public readonly ?string $employeeRef,
    ) {}
}
