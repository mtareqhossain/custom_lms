<?php

namespace App\Filament\Resources;

use App\Filament\Resources\CertificateFontResource\Pages;
use App\Filament\Resources\CertificateFontResource\RelationManagers;
use App\Models\CertificateFont;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class CertificateFontResource extends Resource
{
    protected static ?string $model = CertificateFont::class;

    protected static ?string $navigationIcon = 'heroicon-o-rectangle-stack';

    public static function canViewAny(): bool { return auth()->user()->can('certificate_fonts.manage'); }

    // app/Filament/Resources/CertificateFontResource.php
public static function form(Form $form): Form
{
    return $form->schema([
        Forms\Components\TextInput::make('name')
            ->required()
            ->helperText('A friendly name admins will recognize, e.g. "Signature Script" or "Elegant Serif".'),

        Forms\Components\FileUpload::make('file_path')
            ->label('Font file')
            ->disk('public')
            ->directory('certificates/fonts')
            ->acceptedFileTypes(['.ttf', '.otf'])
            ->required(),
    ]);
}

public static function table(Table $table): Table
{
    return $table
        ->columns([
            Tables\Columns\TextColumn::make('name')->searchable(),
            Tables\Columns\TextColumn::make('file_path')->label('File'),
        ])
        ->actions([
    Tables\Actions\EditAction::make(),
    Tables\Actions\DeleteAction::make(),
])
->bulkActions([
    Tables\Actions\BulkActionGroup::make([
        Tables\Actions\DeleteBulkAction::make(),
    ]),
]);
}

    public static function getPages(): array
    {
        return [
            'index' => Pages\ManageCertificateFonts::route('/'),
        ];
    }
}
