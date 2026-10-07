<?php
namespace App\Filament\Resources\UserResource\Pages;

use App\Exports\UsersExport;
use App\Filament\Resources\UserResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;
use Maatwebsite\Excel\Facades\Excel;

class ListUsers extends ListRecords
{
    protected static string $resource = UserResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\Action::make('export')
                ->label('Export to Excel')
                ->icon('heroicon-o-arrow-down-tray')
                ->action(fn () => Excel::download(new UsersExport, 'users.xlsx')),
            Actions\CreateAction::make(),
        ];
    }
}
