<?php

namespace App\Filament\Resources\ServiceClusterResource\Pages;

use App\Filament\Resources\ServiceClusterResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditServiceCluster extends EditRecord
{
    protected static string $resource = ServiceClusterResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
}
