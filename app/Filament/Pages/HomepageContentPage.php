<?php
namespace App\Filament\Pages;

use App\Models\HomepageContent;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\Section;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;
use Filament\Forms\Form;
use Filament\Notifications\Notification;
use Filament\Pages\Page;

class HomepageContentPage extends Page implements HasForms
{
    use InteractsWithForms;

    protected static ?string $navigationIcon = 'heroicon-o-home';
    protected static ?string $navigationLabel = 'Homepage';
    protected static ?string $title = 'Homepage content';
    protected static string $view = 'filament.pages.homepage-content-page';

    public ?array $data = [];

    public static function canAccess(): bool
    {
        return auth()->user()?->hasRole('admin') ?? false;
    }

    public function mount(): void
    {
        $this->form->fill(HomepageContent::current()->toArray());
    }

    private const ICONS = [
        'home' => 'Home', 'grid' => 'Grid', 'book' => 'Book', 'award' => 'Award',
        'users' => 'Users', 'upload' => 'Upload', 'compass' => 'Compass', 'receipt' => 'Receipt',
    ];

    public function form(Form $form): Form
    {
        return $form->statePath('data')->schema([
            Section::make('Hero')->schema([
                TextInput::make('hero_headline')->required()->maxLength(150),
                Textarea::make('hero_subheadline')->required()->rows(2),
                FileUpload::make('hero_image')->image()->directory('homepage')
                    ->helperText('Leave empty to use the default stock placeholder photo.'),
            ]),

            Section::make('Business section')->description('Shown right after the hero, aimed at business customers.')->schema([
                TextInput::make('business_headline')->required()->maxLength(150),
                Textarea::make('business_subheadline')->required()->rows(2),
                Repeater::make('business_features')->label('Features')->schema([
                    Select::make('icon')->options(self::ICONS)->required(),
                    TextInput::make('title')->required(),
                    TextInput::make('text')->required(),
                ])->columns(3)->reorderableWithButtons()->addActionLabel('Add feature'),
            ]),

            Section::make('Feature strip')->schema([
                Repeater::make('feature_strip')->label('Features')->schema([
                    Select::make('icon')->options(self::ICONS)->required(),
                    TextInput::make('title')->required(),
                    TextInput::make('text')->required(),
                ])->columns(3)->reorderableWithButtons()->addActionLabel('Add feature'),
            ]),

            Section::make('Why us')->schema([
                Repeater::make('why_us_points')->label('Points')->simple(
                    TextInput::make('point')->required()
                )->reorderableWithButtons()->addActionLabel('Add point'),
            ]),

            Section::make('Become an instructor')->schema([
                TextInput::make('instructor_cta_headline')->required()->maxLength(150),
                Textarea::make('instructor_cta_text')->required()->rows(2),
            ]),

            Section::make('Trusted-by logos')->description('Plain text placeholders until you have real client logo images.')->schema([
                Repeater::make('client_logos')->label('Client names')->simple(
                    TextInput::make('name')->required()
                )->reorderableWithButtons()->addActionLabel('Add client'),
            ]),

            Section::make('Testimonials')->schema([
                Repeater::make('testimonials')->schema([
                    Textarea::make('quote')->required()->rows(2),
                    TextInput::make('name')->required(),
                    TextInput::make('role')->required(),
                ])->reorderableWithButtons()->addActionLabel('Add testimonial'),
            ]),

            Section::make('Certifications')->schema([
                Repeater::make('certifications')->label('Certification names')->simple(
                    TextInput::make('name')->required()
                )->reorderableWithButtons()->addActionLabel('Add certification'),
            ]),

            Section::make('Final call to action')->schema([
                TextInput::make('final_cta_headline')->required()->maxLength(150),
                Textarea::make('final_cta_subheadline')->required()->rows(2),
            ]),
        ]);
    }

    public function save(): void
    {
        $data = $this->form->getState();

        // Repeater "simple" fields come back as a flat array of scalars already; nested ones as
        // arrays of associative arrays. Both cast straight to JSON via the model's $casts.
        HomepageContent::query()->updateOrCreate(['id' => 1], $data);

        Notification::make()->title('Homepage updated')->success()->send();
    }
}
