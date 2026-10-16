<?php

namespace App\Filament\Resources\ServiceClusterResource\Pages;

use App\Filament\Resources\ServiceClusterResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListServiceClusters extends ListRecords
{
    protected static string $resource = ServiceClusterResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
