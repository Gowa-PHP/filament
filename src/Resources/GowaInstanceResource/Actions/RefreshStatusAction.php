<?php

namespace Gowa\Filament\Resources\GowaInstanceResource\Actions;

use Exception;
use Filament\Actions\Action;
use Filament\Notifications\Notification;
use Gowa\Laravel\Enums\GowaInstanceStatus;
use Gowa\Laravel\Facades\Gowa;

class RefreshStatusAction
{
    public static function make(): Action
    {
        return Action::make('refreshStatus')
            ->label(__('gowa-filament::gowa-filament.actions.refresh_status'))
            ->icon('heroicon-o-arrow-path')
            ->color('gray')
            ->action(function ($record): void {
                try {
                    $deviceId = $record->device_id ?? (string) $record->getKey();
                    $device = Gowa::device($deviceId);

                    if ($device) {
                        $statusStr = strtolower((string) ($device->status ?? ''));

                        $statusEnum = match (true) {
                            $device->isPaired() || in_array($statusStr, ['logged_in', 'open', 'connected', 'authenticated', 'paired'], true) => GowaInstanceStatus::Open,
                            in_array($statusStr, ['connecting', 'qr_pairing', 'code_pairing', 'login'], true) => GowaInstanceStatus::Connecting,
                            in_array($statusStr, ['close', 'closed', 'disconnected', 'logged_out', 'logout'], true) => GowaInstanceStatus::Close,
                            in_array($statusStr, ['created'], true) => GowaInstanceStatus::Created,
                            default => GowaInstanceStatus::tryFrom($statusStr) ?? $record->status,
                        };

                        $dataToUpdate = [
                            'status' => $statusEnum,
                            'name' => ! empty($device->name) ? $device->name : $record->name,
                            'phone_number' => $device->phone ?? $device->phoneNumber ?? $record->phone_number,
                        ];

                        if ($statusEnum === GowaInstanceStatus::Open && empty($record->connected_at)) {
                            $dataToUpdate['connected_at'] = now();
                        }

                        if (! empty($device->jid)) {
                            $meta = $record->meta ?? [];
                            $meta['jid'] = $device->jid;
                            $dataToUpdate['meta'] = $meta;
                        }

                        $record->update($dataToUpdate);
                    }

                    Notification::make()
                        ->title(__('gowa-filament::gowa-filament.notifications.status_refreshed'))
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
