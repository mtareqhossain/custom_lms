<?php
namespace App\Filament\Resources;

use App\Filament\Resources\OrganizationResource\Pages;
use App\Models\Organization;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Forms\Set;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Support\Str;

class OrganizationResource extends Resource
{
    protected static ?string $model = Organization::class;
    protected static ?string $navigationIcon = 'heroicon-o-building-office';

    public static function canViewAny(): bool
    {
        return auth()->user()?->hasRole('admin') ?? false;
    }

    public static function form(Form $form): Form
    {
        return $form->schema([
            Forms\Components\TextInput::make('name')->required()->maxLength(150)->live(onBlur: true)
                ->afterStateUpdated(fn ($state, Set $set, ?Organization $record) => $record ? null : $set('slug', Str::slug($state))),
            Forms\Components\TextInput::make('slug')->required()->unique(ignoreRecord: true),
            Forms\Components\TextInput::make('contact_email')->email()->required(),
            Forms\Components\Toggle::make('is_active')->default(true),
            Forms\Components\Select::make('courses')->relationship('courses', 'title')->multiple()->searchable()->preload()->columnSpanFull()
                ->helperText('Courses this business may enroll students in.'),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table->columns([
            Tables\Columns\TextColumn::make('name')->searchable()->sortable(),
            Tables\Columns\TextColumn::make('contact_email'),
            Tables\Columns\TextColumn::make('users_count')->counts('users')->label('People'),
            Tables\Columns\TextColumn::make('courses_count')->counts('courses')->label('Courses'),
            Tables\Columns\IconColumn::make('is_active')->boolean(),
        ])->actions([Tables\Actions\EditAction::make()]);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListOrganizations::route('/'),
            'create' => Pages\CreateOrganization::route('/create'),
            'edit' => Pages\EditOrganization::route('/{record}/edit'),
        ];
    }
}
