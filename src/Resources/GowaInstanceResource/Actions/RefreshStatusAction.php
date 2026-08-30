<?php

namespace Gowa\Filament\Resources\GowaInstanceResource\Actions;

use Exception;
use Filament\Notifications\Notification;
use Filament\Tables\Actions\Action;
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
                        $record->update([
                            'status' => $device->status,
                            'name' => $device->name ?? $record->name,
                            'phone' => $device->phone ?? $record->phone,
                            'jid' => $device->jid ?? $record->jid,
                        ]);
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
