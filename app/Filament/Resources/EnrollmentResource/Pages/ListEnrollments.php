<?php
namespace App\Filament\Resources\EnrollmentResource\Pages;

use App\Exports\EnrollmentsExport;
use App\Filament\Resources\EnrollmentResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;
use Maatwebsite\Excel\Facades\Excel;

class ListEnrollments extends ListRecords
{
    protected static string $resource = EnrollmentResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\Action::make('export')
                ->label('Export to Excel (student progress)')
                ->icon('heroicon-o-arrow-down-tray')
                ->action(fn () => Excel::download(new EnrollmentsExport, 'student-progress.xlsx')),
            Actions\CreateAction::make()->label('Enroll a learner'),
        ];
    }
}
