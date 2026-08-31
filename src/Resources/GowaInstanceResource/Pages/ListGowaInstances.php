<?php

namespace Gowa\Filament\Resources\GowaInstanceResource\Pages;

use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;
use Filament\Support\Enums\Width;
use Gowa\Filament\Resources\GowaInstanceResource;
use Gowa\Laravel\Facades\Gowa;
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

                    if (empty($data['webhook_secret'])) {
                        $data['webhook_secret'] = config('gowa.webhook.secret') ?? Str::random(32);
                    }

                    return $data;
                })
                ->after(function (Model $record, ListGowaInstances $livewire): void {
                    $webhookUrl = url(config('gowa.webhook.path', 'webhooks/gowa') . '/' . $record->device_id);
                    $webhookSecret = $record->webhook_secret ?? config('gowa.webhook.secret') ?? Str::random(32);

                    try {
                        Gowa::createDevice(
                            deviceId: (string) $record->device_id,
                            webhookUrl: $webhookUrl,
                            webhookSecret: $webhookSecret,
                            events: ['message', 'message.ack', 'message.reaction', 'device.status']
                        );
                    } catch (\Throwable $e) {
                        // Silently handle if device is registered during pairing
                    }

                    $livewire->mountTableAction('connectQr', (string) $record->getKey());
                }),
        ];
    }
}
