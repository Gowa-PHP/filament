<?php

use Gowa\Filament\GowaPlugin;

it('has correct plugin id', function () {
    $plugin = GowaPlugin::make();

    expect($plugin->getId())->toBe('gowa-filament');
});

it('can instantiate plugin via make static method', function () {
    $plugin = GowaPlugin::make();

    expect($plugin)->toBeInstanceOf(GowaPlugin::class);
});
