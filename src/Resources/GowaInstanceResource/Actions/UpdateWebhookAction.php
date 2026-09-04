<?php

declare(strict_types=1);

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
                    $insecureSkipVerify = (bool) ($record->meta['webhook_insecure_skip_verify'] ?? false);

                    Gowa::updateWebhook(
                        deviceId: $deviceId,
                        webhookUrl: $webhookUrl,
                        webhookSecret: $record->webhook_secret ?? '',
                        events: ['message', 'message.ack', 'message.reaction', 'device.status'],
                        insecureSkipVerify: $insecureSkipVerify,
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
