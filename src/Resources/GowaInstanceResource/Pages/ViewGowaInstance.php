<?php

declare(strict_types=1);

namespace Gowa\Filament\Resources\GowaInstanceResource\Pages;

use Filament\Actions\EditAction;
use Filament\Resources\Pages\ViewRecord;
use Gowa\Filament\Resources\GowaInstanceResource;

class ViewGowaInstance extends ViewRecord
{
    protected static string $resource = GowaInstanceResource::class;

    protected function getHeaderActions(): array
    {
        return [
            EditAction::make(),
        ];
    }
}
