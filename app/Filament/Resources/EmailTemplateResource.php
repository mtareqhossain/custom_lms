<?php

namespace App\Filament\Resources;

use App\Filament\Resources\EmailTemplateResource\Pages;
use App\Models\EmailTemplate;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class EmailTemplateResource extends Resource
{
    protected static ?string $model = EmailTemplate::class;

    protected static ?string $navigationIcon = 'heroicon-o-envelope';

    protected static ?string $navigationGroup = 'Notifications';

    protected static ?string $navigationLabel = 'Email Templates';

    protected static ?int $navigationSort = 1;

    public static function canCreate(): bool { return auth()->user()->can('email_templates.manage'); }
public static function canViewAny(): bool { return auth()->user()->can('email_templates.manage'); }

    public static function form(Form $form): Form
    {
        return $form->schema([
            Forms\Components\Section::make('Template')
                ->columns(2)
                ->schema([
                    Forms\Components\TextInput::make('name')
                        ->required()
                        ->maxLength(255)
                        ->columnSpan(1),
                    Forms\Components\TextInput::make('slug')
                        ->required()
                        ->maxLength(255)
                        ->unique(ignoreRecord: true)
                        ->disabled(fn (?EmailTemplate $record) => $record?->is_system === true)
                        ->helperText('Used internally to trigger this email. Do not change for system templates.')
                        ->columnSpan(1),
                    Forms\Components\Textarea::make('description')
                        ->rows(2)
                        ->columnSpanFull()
                        ->helperText('Shown here in admin only — not visible to recipients.'),
                    Forms\Components\Toggle::make('is_active')
                        ->default(true)
                        ->helperText('Turn off to pause this email without deleting it.')
                        ->columnSpanFull(),
                ]),

            Forms\Components\Section::make('Content')
                ->schema([
                    Forms\Components\TextInput::make('subject')
                        ->required()
                        ->maxLength(255),
                    Forms\Components\RichEditor::make('body')
                        ->required()
                        ->fileAttachmentsDirectory('email-attachments')
                        ->helperText(function (?EmailTemplate $record) {
                            $placeholders = $record?->available_placeholders ?? [];
                            if (empty($placeholders)) {
                                return 'Use {{student_name}} style placeholders where supported.';
                            }
                            $list = collect($placeholders)->map(fn ($p) => '{{' . $p . '}}')->implode(', ');
                            return "Available placeholders for this template: {$list}";
                        }),
                ]),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('name')->searchable()->sortable(),
                Tables\Columns\TextColumn::make('slug')->badge()->color('gray'),
                Tables\Columns\IconColumn::make('is_active')->boolean()->label('Active'),
                Tables\Columns\IconColumn::make('is_system')->boolean()->label('System')
                    ->trueIcon('heroicon-o-lock-closed')->falseIcon('heroicon-o-pencil'),
                Tables\Columns\TextColumn::make('updated_at')->dateTime()->sortable(),
            ])
            ->filters([
                Tables\Filters\TernaryFilter::make('is_active'),
            ])
            ->actions([
    Tables\Actions\EditAction::make(),
    Tables\Actions\DeleteAction::make()
        ->visible(fn (EmailTemplate $record) => !$record->is_system),
])
->bulkActions([
    Tables\Actions\BulkActionGroup::make([
        Tables\Actions\DeleteBulkAction::make()
            ->action(function ($records) {
                $protected = $records->where('is_system', true)->count();
                $records->where('is_system', false)->each->delete();

                if ($protected) {
                    \Filament\Notifications\Notification::make()
                        ->title("Skipped {$protected} system template(s) — those can't be deleted.")
                        ->warning()
                        ->send();
                }
            }),
    ]),
]);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListEmailTemplates::route('/'),
            'create' => Pages\CreateEmailTemplate::route('/create'),
            'edit' => Pages\EditEmailTemplate::route('/{record}/edit'),
        ];
    }
}
