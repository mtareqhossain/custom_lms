<?php
// app/Filament/Resources/RoleResource.php
namespace App\Filament\Resources;

use App\Filament\Resources\RoleResource\Pages;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

class RoleResource extends Resource
{
    protected static ?string $model = Role::class;
    protected static ?string $navigationIcon = 'heroicon-o-shield-check';
    protected static ?string $navigationGroup = 'Administration';

    public static function canViewAny(): bool
    {
        return auth()->user()->hasRole('admin');
    }

    public static function form(Form $form): Form
    {
        return $form->schema([
            Forms\Components\TextInput::make('name')
                ->required()
                ->unique(ignoreRecord: true)
                ->disabled(fn (?Role $record) => $record && in_array($record->name, ['admin', 'student']))
                ->helperText('Lowercase, no spaces — e.g. "business" or "support_agent"'),

            Forms\Components\CheckboxList::make('permissions')
    ->relationship('permissions', 'name')
    ->options(fn () => Permission::pluck('name', 'id'))
    ->columns(3)
    ->searchable()
    ->bulkToggleable(),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('name')->searchable(),
                Tables\Columns\TextColumn::make('permissions_count')->counts('permissions')->label('Permissions'),
                Tables\Columns\TextColumn::make('users_count')->counts('users')->label('Users'),
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make()
                    ->disabled(fn (Role $record) => in_array($record->name, ['admin', 'student', 'instructor', 'business'])),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListRoles::route('/'),
            'create' => Pages\CreateRole::route('/create'),
            'edit' => Pages\EditRole::route('/{record}/edit'),
        ];
    }
}
