<?php

declare(strict_types=1);

namespace Gowa\Filament\Resources\GowaInstanceResource\Pages;

use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;
use Gowa\Filament\Resources\GowaInstanceResource;

class EditGowaInstance extends EditRecord
{
    protected static string $resource = GowaInstanceResource::class;

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make(),
        ];
    }
}
