<?php

namespace Gowa\Filament\Resources\GowaInstanceResource\Actions;

use Filament\Actions\Action;
use Filament\Support\Enums\Width;
use Illuminate\Contracts\View\View;

class ConnectQrAction
{
    public static function make(): Action
    {
        return Action::make('connectQr')
            ->label(__('gowa-filament::gowa-filament.actions.connect_qr'))
            ->icon('heroicon-o-qr-code')
            ->color('success')
            ->modalHeading(__('gowa-filament::gowa-filament.qr.title'))
            ->modalWidth(Width::Small)
            ->modalContent(fn ($record): View => view('gowa-filament::actions.connect-qr-modal', [
                'deviceId' => $record->device_id ?? (string) $record->getKey(),
            ]))
            ->modalSubmitAction(false)
            ->modalCancelActionLabel(__('gowa-filament::gowa-filament.actions.close'));
    }
}
