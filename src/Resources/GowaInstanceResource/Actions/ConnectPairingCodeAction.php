<?php

namespace Gowa\Filament\Resources\GowaInstanceResource\Actions;

use Filament\Tables\Actions\Action;
use Illuminate\Contracts\View\View;

class ConnectPairingCodeAction
{
    public static function make(): Action
    {
        return Action::make('connectPairingCode')
            ->label(__('gowa-filament::gowa-filament.actions.connect_code'))
            ->icon('heroicon-o-device-phone-mobile')
            ->color('info')
            ->modalTitle(__('gowa-filament::gowa-filament.pairing.title'))
            ->modalContent(fn ($record): View => view('gowa-filament::livewire.gowa-pairing-code', [
                'deviceId' => $record->device_id ?? (string) $record->getKey(),
            ]))
            ->modalSubmitAction(false)
            ->modalCancelActionLabel('Close');
    }
}
