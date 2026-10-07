<?php
namespace App\Filament\Pages;

use App\Jobs\ImportContentJob;
use App\Models\BulkImport;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;
use Filament\Forms\Form;
use Filament\Notifications\Notification;
use Filament\Pages\Page;
use Illuminate\Support\Collection;

class BulkContentImport extends Page implements HasForms
{
    use InteractsWithForms;

    protected static ?string $navigationIcon = 'heroicon-o-arrow-up-tray';
    protected static ?string $navigationLabel = 'Bulk content import';
    protected static ?string $title = 'Bulk content import';
    protected static string $view = 'filament.pages.bulk-content-import';

    public ?array $data = [];

    public static function canAccess(): bool
    {
        return auth()->user()?->hasRole('admin') ?? false;
    }

    public function mount(): void
    {
        $this->form->fill();
    }

    public function form(Form $form): Form
    {
        return $form->statePath('data')->schema([
            FileUpload::make('sheet')->label('Spreadsheet (.xlsx or .csv)')->required()
                ->disk('local')->directory('bulk-imports')->visibility('private')
                ->acceptedFileTypes([
                    'text/csv', 'text/plain', 'application/vnd.ms-excel',
                    'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
                ]),
            FileUpload::make('media')->label('Media ZIP (optional): thumbnails, PDFs, attachments')
                ->disk('local')->directory('bulk-imports')->visibility('private')
                ->acceptedFileTypes(['application/zip', 'application/x-zip-compressed']),
        ]);
    }

    public function submit(): void
    {
        $data = $this->form->getState();

        $import = BulkImport::create([
            'user_id' => auth()->id(),
            'type' => 'courses',
            'file_path' => $data['sheet'],
            'status' => 'pending',
        ]);

        ImportContentJob::dispatch($import->id, $data['media'] ?? null);

        $this->form->fill();
        Notification::make()->title('Import started')->body('Refresh this page in a moment to see the result.')->success()->send();
    }

    public function recentImports(): Collection
    {
        return BulkImport::where('type', 'courses')->latest()->limit(10)->get();
    }
}
