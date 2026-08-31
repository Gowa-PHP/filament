<?php

namespace Gowa\Filament\Resources\GowaInstanceResource\Actions;

use Filament\Actions\Action;
use Filament\Support\Enums\Width;
use Illuminate\Contracts\View\View;

class ConnectPairingCodeAction
{
    public static function make(): Action
    {
        return Action::make('connectPairingCode')
            ->label(__('gowa-filament::gowa-filament.actions.connect_pairing_code'))
            ->icon('heroicon-o-device-phone-mobile')
            ->color('primary')
            ->modalHeading(__('gowa-filament::gowa-filament.pairing.title'))
            ->modalWidth(Width::Small)
            ->modalContent(fn ($record): View => view('gowa-filament::actions.connect-pairing-modal', [
                'deviceId' => $record->device_id ?? (string) $record->getKey(),
            ]))
            ->modalSubmitAction(false)
            ->modalCancelActionLabel(__('gowa-filament::gowa-filament.actions.close'));
    }
}
