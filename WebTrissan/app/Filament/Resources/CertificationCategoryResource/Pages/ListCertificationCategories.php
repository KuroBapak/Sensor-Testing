<?php

namespace App\Filament\Resources\CertificationCategoryResource\Pages;

use App\Filament\Resources\CertificationCategoryResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListCertificationCategories extends ListRecords
{
    protected static string $resource = CertificationCategoryResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
