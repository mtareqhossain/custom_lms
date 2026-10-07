<?php
namespace App\Filament\Resources;

use App\Filament\Resources\UserResource\Pages;
use App\Models\User;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Support\Facades\Hash;

class UserResource extends Resource
{
    protected static ?string $model = User::class;
    protected static ?string $navigationIcon = 'heroicon-o-users';

    public static function canViewAny(): bool
    {
        return auth()->user()?->hasRole('admin') ?? false;
    }

    public static function form(Form $form): Form
    {
        return $form->schema([
            Forms\Components\TextInput::make('name')->required()->maxLength(150),
            Forms\Components\TextInput::make('email')->email()->required()->unique(ignoreRecord: true),
            Forms\Components\TextInput::make('password')->password()->revealable()
                ->required(fn (string $operation) => $operation === 'create')
                ->dehydrated(fn ($state) => filled($state))
                ->dehydrateStateUsing(fn ($state) => Hash::make($state))
                ->helperText('Leave blank on edit to keep the current password.'),
            Forms\Components\Select::make('organization_id')->relationship('organization', 'name')->searchable()->preload()
                ->helperText('Set this for business admins and their students.'),
            Forms\Components\Select::make('roles')->relationship('roles', 'name')->multiple()->preload(),
            Forms\Components\Toggle::make('is_active')->default(true),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table->columns([
            Tables\Columns\TextColumn::make('name')->searchable()->sortable(),
            Tables\Columns\TextColumn::make('email')->searchable(),
            Tables\Columns\TextColumn::make('roles.name')->badge(),
            Tables\Columns\TextColumn::make('organization.name')->placeholder('None'),
            Tables\Columns\IconColumn::make('is_active')->boolean(),
        ])->filters([
            Tables\Filters\SelectFilter::make('roles')->relationship('roles', 'name'),
        ])->actions([Tables\Actions\EditAction::make()]);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListUsers::route('/'),
            'create' => Pages\CreateUser::route('/create'),
            'edit' => Pages\EditUser::route('/{record}/edit'),
        ];
    }
}
