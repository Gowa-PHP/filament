<?php

namespace Gowa\Filament\Resources\GowaInstanceResource\Pages;

use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;
use Gowa\Filament\Resources\GowaInstanceResource;

class ListGowaInstances extends ListRecords
{
    protected static string $resource = GowaInstanceResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}
