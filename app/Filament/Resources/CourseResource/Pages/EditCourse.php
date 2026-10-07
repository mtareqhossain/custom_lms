<?php
namespace App\Filament\Resources\CourseResource\Pages;

use App\Filament\Resources\CourseResource;
use App\Jobs\ImportLessonsJob;
use App\Models\BulkImport;
use Filament\Actions;
use Filament\Forms;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\EditRecord;

/** Replaces the Phase 2 EditCourse page: adds the "Import lessons" action. */
class EditCourse extends EditRecord
{
    protected static string $resource = CourseResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\Action::make('importLessons')
                ->label('Import lessons')
                ->icon('heroicon-o-arrow-up-tray')
                ->form([
                    Forms\Components\FileUpload::make('sheet')->label('Spreadsheet (.xlsx or .csv)')->required()
                        ->disk('local')->directory('bulk-imports')->visibility('private')
                        ->acceptedFileTypes([
                            'text/csv', 'text/plain', 'application/vnd.ms-excel',
                            'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
                        ]),
                    Forms\Components\FileUpload::make('media')->label('Attachment ZIP (optional)')
                        ->disk('local')->directory('bulk-imports')->visibility('private')
                        ->acceptedFileTypes(['application/zip', 'application/x-zip-compressed']),
                    Forms\Components\Placeholder::make('help')->label('')
                        ->content(fn () => new \Illuminate\Support\HtmlString(
                            'Required columns: <code>section</code>, <code>lesson_title</code>. '.
                            '<a href="'.asset('templates/lesson-import-template.csv').'" target="_blank" class="underline">Download the template</a>.'
                        )),
                ])
                ->action(function (array $data) {
                    $import = BulkImport::create([
                        'user_id' => auth()->id(),
                        'organization_id' => null,
                        'type' => 'lessons',
                        'file_path' => $data['sheet'],
                        'status' => 'pending',
                    ]);

                    ImportLessonsJob::dispatch($import->id, $this->record->id, $data['media'] ?? null);

                    Notification::make()->title('Import started')->body('Refresh this page in a moment to see the result.')->success()->send();
                }),
            Actions\DeleteAction::make(),
        ];
    }
    protected function afterSave(): void
{
    foreach ($this->record->sections as $section) {
        foreach ($section->lessons as $lesson) {
            app(\App\Services\ContentIndexer::class)->indexLesson($lesson);
        }
    }
}
}
