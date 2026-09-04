<?php

declare(strict_types=1);

namespace Gowa\Filament\Widgets;

use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class GowaDeviceStatusWidget extends BaseWidget
{
    protected ?string $pollingInterval = '5s';

    protected function getStats(): array
    {
        /** @var class-string<\Illuminate\Database\Eloquent\Model> $model */
        $model = config('gowa-filament.model', \Gowa\Laravel\Models\GowaInstance::class);

        $total = $model::count();
        $connected = $model::whereIn('status', ['open', 'connected'])->count();
        $connecting = $model::where('status', 'connecting')->count();
        $disconnected = $model::whereIn('status', ['close', 'disconnected', 'created'])->count();

        return [
            Stat::make(
                __('gowa-filament::gowa-filament.widgets.total_instances'),
                (string) $total,
            )
                ->icon('heroicon-o-rectangle-stack')
                ->color('gray'),

            Stat::make(
                __('gowa-filament::gowa-filament.widgets.connected_instances'),
                (string) $connected,
            )
                ->icon('heroicon-o-check-circle')
                ->color('success'),

            Stat::make(
                __('gowa-filament::gowa-filament.widgets.connecting_instances'),
                (string) $connecting,
            )
                ->icon('heroicon-o-arrow-path')
                ->color('warning'),

            Stat::make(
                __('gowa-filament::gowa-filament.widgets.disconnected_instances'),
                (string) $disconnected,
            )
                ->icon('heroicon-o-x-circle')
                ->color('danger'),
        ];
    }
}
