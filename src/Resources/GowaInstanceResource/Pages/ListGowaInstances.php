<?php

namespace Gowa\Filament\Resources\GowaInstanceResource\Pages;

use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;
use Filament\Support\Enums\Width;
use Gowa\Filament\Resources\GowaInstanceResource;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class ListGowaInstances extends ListRecords
{
    protected static string $resource = GowaInstanceResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make()
                ->modalHeading(__('gowa-filament::gowa-filament.actions.create_heading'))
                ->modalDescription(__('gowa-filament::gowa-filament.actions.create_desc'))
                ->modalWidth(Width::Medium)
                ->createAnother(false)
                ->mutateFormDataUsing(function (array $data): array {
                    if (empty($data['device_id'])) {
                        $data['device_id'] = (string) Str::uuid7();
                    }

                    return $data;
                })
                ->after(function (Model $record, ListGowaInstances $livewire): void {
                    $livewire->mountTableAction('connectQr', (string) $record->getKey());
                }),
        ];
    }
}
