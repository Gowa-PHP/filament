<?php

namespace Gowa\Filament;

use Gowa\Filament\Livewire\GowaPairingCode;
use Gowa\Filament\Livewire\GowaQrCode;
use Illuminate\Support\ServiceProvider;
use Livewire\Livewire;

class GowaFilamentServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->mergeConfigFrom(
            __DIR__ . '/../config/gowa-filament.php',
            'gowa-filament'
        );
    }

    public function boot(): void
    {
        $this->loadViewsFrom(__DIR__ . '/../resources/views', 'gowa-filament');
        $this->loadTranslationsFrom(__DIR__ . '/../resources/lang', 'gowa-filament');

        if (class_exists(Livewire::class)) {
            Livewire::component('gowa-qr-code', GowaQrCode::class);
            Livewire::component('gowa-pairing-code', GowaPairingCode::class);
        }

        if ($this->app->runningInConsole()) {
            $this->publishes([
                __DIR__ . '/../config/gowa-filament.php' => config_path('gowa-filament.php'),
            ], 'gowa-filament-config');

            $this->publishes([
                __DIR__ . '/../resources/views' => resource_path('views/vendor/gowa-filament'),
            ], 'gowa-filament-views');

            $this->publishes([
                __DIR__ . '/../resources/lang' => $this->app->langPath('vendor/gowa-filament'),
            ], 'gowa-filament-translations');
        }
    }
}
