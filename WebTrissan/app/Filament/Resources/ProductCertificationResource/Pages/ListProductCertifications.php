<?php

namespace App\Filament\Resources\ProductCertificationResource\Pages;

use App\Filament\Resources\ProductCertificationResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListProductCertifications extends ListRecords
{
    protected static string $resource = ProductCertificationResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
