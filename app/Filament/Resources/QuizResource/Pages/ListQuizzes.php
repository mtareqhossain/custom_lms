<?php

namespace App\Filament\Resources\QuizResource\Pages;

use App\Filament\Resources\QuizResource;
use App\Imports\QuizImport;
use App\Jobs\ImportQuizzesJob;
use App\Models\QuizImportBatch;
use Filament\Actions;
use Filament\Forms;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\ListRecords;
use Illuminate\Support\Facades\Storage;

class ListQuizzes extends ListRecords
{
    protected static string $resource = QuizResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\Action::make('export')
                ->label('Export quizzes')
                ->icon('heroicon-o-arrow-down-tray')
                ->action(function () {
                    return response()->streamDownload(function () {
                        echo \Maatwebsite\Excel\Facades\Excel::raw(
                            new \App\Exports\QuizzesExport(),
                            \Maatwebsite\Excel\Excel::XLSX
                        );
                    }, 'quizzes-export.xlsx');
                }),

            Actions\Action::make('import')
                ->label('Import quizzes')
                ->icon('heroicon-o-arrow-up-tray')
                ->form([
                    Forms\Components\FileUpload::make('file')
                        ->label('Excel file')
                        ->disk('local')
                        ->directory('quiz-imports')
                        ->acceptedFileTypes(['application/vnd.openxmlformats-officedocument.spreadsheetml.sheet', 'text/csv'])
                        ->required(),
                ])
                ->action(function (array $data) {
                    $batch = QuizImportBatch::create([
                        'file_path' => $data['file'],
                        'status' => 'pending',
                        'created_by' => auth()->id(),
                    ]);

                    ImportQuizzesJob::dispatch($batch->id);

                    Notification::make()
                        ->title('Import queued — check back shortly for results')
                        ->success()
                        ->send();
                }),

            Actions\CreateAction::make(),
        ];
    }
}
