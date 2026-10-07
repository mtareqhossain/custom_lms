<?php
namespace App\Exports;

use App\Models\User;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;

class UsersExport implements FromCollection, WithHeadings, WithMapping
{
    public function collection()
    {
        return User::with(['roles', 'organization'])->orderBy('name')->get();
    }

    public function headings(): array
    {
        return ['Name', 'Email', 'Role(s)', 'Organization', 'Active', 'Joined'];
    }

    public function map($user): array
    {
        return [
            $user->name,
            $user->email,
            $user->roles->pluck('name')->implode(', '),
            $user->organization?->name ?? '',
            $user->is_active ? 'Yes' : 'No',
            $user->created_at->format('Y-m-d'),
        ];
    }
}
