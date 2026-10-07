<?php

namespace App\Filament\Resources\CertificateFontResource\Pages;

use App\Filament\Resources\CertificateFontResource;
use Filament\Actions;
use Filament\Resources\Pages\ManageRecords;

class ManageCertificateFonts extends ManageRecords
{
    protected static string $resource = CertificateFontResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
