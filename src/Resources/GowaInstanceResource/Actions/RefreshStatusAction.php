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
                        $statusEnum = GowaInstanceStatus::tryFrom($statusStr) ?? match ($statusStr) {
                            'connected', 'open' => GowaInstanceStatus::Open,
                            'disconnected', 'closed', 'close' => GowaInstanceStatus::Close,
                            'connecting' => GowaInstanceStatus::Connecting,
                            default => $record->status,
                        };

                        $dataToUpdate = [
                            'status' => $statusEnum,
                            'name' => ! empty($device->name) ? $device->name : $record->name,
                            'phone_number' => $device->phone ?? $device->phoneNumber ?? $record->phone_number,
                        ];

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
