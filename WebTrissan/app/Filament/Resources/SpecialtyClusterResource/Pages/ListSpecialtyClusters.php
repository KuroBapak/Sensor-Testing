<?php

namespace App\Filament\Resources\SpecialtyClusterResource\Pages;

use App\Filament\Resources\SpecialtyClusterResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListSpecialtyClusters extends ListRecords
{
    protected static string $resource = SpecialtyClusterResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
