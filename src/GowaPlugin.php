<?php

namespace Gowa\Filament;

use Filament\Contracts\Plugin;
use Filament\Panel;
use Gowa\Filament\Pages\GowaMessagingPage;
use Gowa\Filament\Resources\GowaInstanceResource;
use Gowa\Filament\Widgets\GowaDeviceStatusWidget;

class GowaPlugin implements Plugin
{
    protected bool $hasInstanceResource = true;
    protected bool $hasDeviceStatusWidget = true;
    protected bool $hasMessagingPage = true;

    public function getId(): string
    {
        return 'gowa-filament';
    }

    public static function make(): static
    {
        return app(static::class);
    }

    public static function get(): static
    {
        /** @var static $plugin */
        $plugin = filament()->getPlugin(app(static::class)->getId());

        return $plugin;
    }

    public function instanceResource(bool $condition = true): static
    {
        $this->hasInstanceResource = $condition;

        return $this;
    }

    public function hasInstanceResource(): bool
    {
        return $this->hasInstanceResource;
    }

    public function deviceStatusWidget(bool $condition = true): static
    {
        $this->hasDeviceStatusWidget = $condition;

        return $this;
    }

    public function hasDeviceStatusWidget(): bool
    {
        return $this->hasDeviceStatusWidget;
    }

    public function messagingPage(bool $condition = true): static
    {
        $this->hasMessagingPage = $condition;

        return $this;
    }

    public function hasMessagingPage(): bool
    {
        return $this->hasMessagingPage;
    }

    public function register(Panel $panel): void
    {
        if ($this->hasInstanceResource) {
            $panel->resources([
                GowaInstanceResource::class,
            ]);
        }

        if ($this->hasMessagingPage) {
            $panel->pages([
                GowaMessagingPage::class,
            ]);
        }

        if ($this->hasDeviceStatusWidget) {
            $panel->widgets([
                GowaDeviceStatusWidget::class,
            ]);
        }
    }

    public function boot(Panel $panel): void
    {
        // Panel boot hooks if needed
    }
}
