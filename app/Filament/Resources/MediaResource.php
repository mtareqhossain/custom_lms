<?php
namespace App\Filament\Resources;

use App\Filament\Resources\MediaResource\Pages;
use App\Models\Media;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Support\Facades\Auth;

class MediaResource extends Resource
{
    protected static ?string $model = Media::class;
    protected static ?string $navigationIcon = 'heroicon-o-photo';
    protected static ?string $navigationGroup = 'Content';
    protected static ?string $navigationLabel = 'Media Library';

    public static function canAccess(): bool
    {
        return (bool) Auth::user()?->hasRole('admin');
    }

    public static function form(Form $form): Form
    {
        return $form->schema([
            Forms\Components\TextInput::make('filename')->required(),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->contentGrid(['md' => 3, 'xl' => 4])
            ->defaultSort('created_at', 'desc')
            ->columns([
                Tables\Columns\ImageColumn::make('path')
                    ->label('')
                    ->disk(fn (Media $record) => $record->disk)
                    ->height(160)
                    ->width('100%')
                    ->extraImgAttributes(['class' => 'object-cover rounded-lg']),
                Tables\Columns\TextColumn::make('filename')->searchable()->limit(30)->weight('medium'),
                Tables\Columns\TextColumn::make('size')
                    ->formatStateUsing(fn ($state) => $state ? number_format($state / 1024, 1).' KB' : '—')
                    ->color('gray'),
                Tables\Columns\TextColumn::make('created_at')->since()->color('gray'),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('type')
                    ->options(['image' => 'Images', 'other' => 'Other files'])
                    ->query(fn ($query, $data) => match ($data['value'] ?? null) {
                        'image' => $query->where('mime_type', 'like', 'image/%'),
                        'other' => $query->where(fn ($q) => $q->whereNull('mime_type')->orWhere('mime_type', 'not like', 'image/%')),
                        default => $query,
                    }),
            ])
            ->headerActions([
                Tables\Actions\Action::make('upload')
                    ->label('Upload to library')
                    ->icon('heroicon-o-arrow-up-tray')
                    ->form([
                        Forms\Components\FileUpload::make('files')
                            ->multiple()
                            ->directory('media-library')
                            ->saveUploadedFileUsing(fn ($file) => Media::storeUpload($file, 'media-library')->path),
                    ])
                    ->action(fn () => null), // files are already stored + registered by saveUploadedFileUsing above
            ])
            ->actions([
                Tables\Actions\DeleteAction::make()
                    ->using(fn (Media $record) => $record->deleteWithFile()),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\BulkAction::make('delete')
                        ->label('Delete selected')
                        ->icon('heroicon-o-trash')
                        ->requiresConfirmation()
                        ->action(fn ($records) => $records->each->deleteWithFile()),
                ]),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListMedia::route('/'),
        ];
    }
}
