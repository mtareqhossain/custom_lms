<?php
namespace App\Filament\Support;

use App\Models\Media;
use Filament\Forms;
use Filament\Forms\Components\Actions\Action;
use Filament\Forms\Get;
use Filament\Forms\Set;
use Illuminate\Support\HtmlString;

class MediaLibraryAction
{
    /**
     * A hint action for a FileUpload field: opens a modal to pick an existing
     * file from the shared library and writes its path into $targetField,
     * exactly as if it had just been uploaded through that field.
     *
     * $mimeFilter defaults to images only — pass null to show every file type.
     */
    public static function make(string $targetField, ?string $mimeFilter = 'image/%'): Action
    {
        return Action::make('chooseFromLibrary')
            ->label('Choose from library')
            ->icon('heroicon-o-photo')
            ->modalHeading('Media library')
            ->modalWidth('2xl')
            ->form([
                Forms\Components\Select::make('media_id')
                    ->label('File')
                    ->options(fn () => Media::query()
                        ->when($mimeFilter, fn ($q) => $q->where('mime_type', 'like', $mimeFilter))
                        ->orderByDesc('created_at')
                        ->pluck('filename', 'id'))
                    ->searchable()
                    ->required()
                    ->live(),
                Forms\Components\Placeholder::make('preview')
                    ->label('')
                    ->content(function (Get $get) {
                        $media = $get('media_id') ? Media::find($get('media_id')) : null;

                        return $media
                            ? new HtmlString('<img src="'.$media->url().'" class="h-48 rounded-lg border object-contain">')
                            : new HtmlString('<p class="text-sm text-gray-500">Select a file to preview it.</p>');
                    }),
            ])
            ->action(function (array $data, Set $set) {
                $media = Media::find($data['media_id']);
                $set($targetField, $media?->path);
            });
    }
}
