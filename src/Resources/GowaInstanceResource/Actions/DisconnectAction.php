<?php

namespace Gowa\Filament\Resources\GowaInstanceResource\Actions;

use Exception;
use Filament\Notifications\Notification;
use Filament\Actions\Action;
use Gowa\Laravel\Facades\Gowa;

class DisconnectAction
{
    public static function make(): Action
    {
        return Action::make('disconnect')
            ->label(__('gowa-filament::gowa-filament.actions.disconnect'))
            ->icon('heroicon-o-power')
            ->color('danger')
            ->requiresConfirmation()
            ->modalDescription(__('gowa-filament::gowa-filament.actions.disconnect_confirm'))
            ->action(function ($record): void {
                try {
                    $deviceId = $record->device_id ?? (string) $record->getKey();
                    Gowa::logout($deviceId);

                    $record->update([
                        'status' => 'close',
                    ]);

                    Notification::make()
                        ->title(__('gowa-filament::gowa-filament.notifications.disconnected_success'))
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
