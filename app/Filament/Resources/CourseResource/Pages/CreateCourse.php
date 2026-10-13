<?php
namespace App\Filament\Resources\CourseResource\Pages;

use App\Filament\Resources\CourseResource;
use Filament\Resources\Pages\CreateRecord;

class CreateCourse extends CreateRecord
{
    protected static string $resource = CourseResource::class;

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        return CourseResource::prepare($data);
    }

    protected function afterCreate(): void
    {
        app(\App\Services\StripePriceSync::class)->sync($this->record);

        foreach ($this->record->sections as $section) {
            foreach ($section->lessons as $lesson) {
                app(\App\Services\ContentIndexer::class)->indexLesson($lesson);
            }
        }
    }
}
