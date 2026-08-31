<?php

namespace Gowa\Filament\Resources\GowaInstanceResource\Actions;

use Exception;
use Filament\Actions\Action;
use Filament\Notifications\Notification;
use Gowa\Laravel\Facades\Gowa;

class UpdateWebhookAction
{
    public static function make(): Action
    {
        return Action::make('updateWebhook')
            ->label(__('gowa-filament::gowa-filament.actions.update_webhook', ['default' => 'Sincronizar Webhook']))
            ->icon('heroicon-o-link')
            ->color('info')
            ->action(function ($record): void {
                try {
                    $deviceId = $record->device_id ?? (string) $record->getKey();
                    $webhookUrl = url(config('gowa.webhook.path', 'webhooks/gowa') . '/' . $deviceId);

                    $record->update(['webhook_secret' => null]);

                    Gowa::updateWebhook(
                        deviceId: $deviceId,
                        webhookUrl: $webhookUrl,
                        webhookSecret: '',
                        events: ['message', 'message.ack', 'message.reaction', 'device.status']
                    );

                    Notification::make()
                        ->title(__('gowa-filament::gowa-filament.notifications.webhook_updated', ['default' => 'URL do Webhook sincronizada no GOWA com sucesso!']))
                        ->success()
                        ->send();
                } catch (Exception $e) {
                    Notification::make()
                        ->title(__('gowa-filament::gowa-filament.notifications.error_occurred'))
                        ->body($e->getMessage())
                        ->danger()
                        ->send();
                }
            });
    }
}
