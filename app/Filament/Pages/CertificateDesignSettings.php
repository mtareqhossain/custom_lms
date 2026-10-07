<?php

namespace App\Filament\Pages;

use App\Models\CertificateSetting;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Notifications\Notification;
use Filament\Pages\Page;

class CertificateDesignSettings extends Page implements Forms\Contracts\HasForms
{
    use Forms\Concerns\InteractsWithForms;

    protected static ?string $navigationIcon = 'heroicon-o-identification';
    protected static ?string $navigationGroup = 'Settings';
    protected static ?string $navigationLabel = 'Certificate Design';
    protected static ?string $title = 'Certificate Design';
    protected static string $view = 'filament.pages.certificate-design-settings';

    public ?array $data = [];

    public function mount(): void
    {
        $setting = CertificateSetting::current();

        $this->form->fill([
            'background_path' => $setting->background_path,
            'badge_path' => $setting->badge_path,
        ]);
    }

    public function form(Form $form): Form
    {
        return $form->schema([
            Forms\Components\FileUpload::make('background_path')
                ->label('Certificate background')
                ->image()
                ->imageEditor()
                ->directory('certificates/backgrounds')
                ->helperText('Full certificate background, landscape. Used by any course that doesn\'t set its own background below.'),

            Forms\Components\FileUpload::make('badge_path')
                ->label('Accreditation badge (top-right corner)')
                ->image()
                ->directory('certificates/badges')
                ->helperText('Square image, shown fixed-size in the top-right corner, overlaid on top of the background.'),
        ])->statePath('data');
    }

    public function save(): void
    {
        CertificateSetting::current()->update($this->form->getState());

        Notification::make()->title('Certificate design saved')->success()->send();
    }
}
