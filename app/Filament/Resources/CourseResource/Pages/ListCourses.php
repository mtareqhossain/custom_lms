<?php
namespace App\Filament\Resources\CourseResource\Pages;

use App\Exports\CoursesExport;
use App\Filament\Resources\CourseResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;
use Maatwebsite\Excel\Facades\Excel;

class ListCourses extends ListRecords
{
    protected static string $resource = CourseResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\Action::make('export')
                ->label('Export to Excel')
                ->icon('heroicon-o-arrow-down-tray')
                ->action(fn () => Excel::download(new CoursesExport, 'courses.xlsx')),
            Actions\CreateAction::make(),
        ];
    }
}
