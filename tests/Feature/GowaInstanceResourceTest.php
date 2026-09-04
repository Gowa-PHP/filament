<?php

declare(strict_types=1);

use Gowa\Filament\Resources\GowaInstanceResource;
use Gowa\Laravel\Models\GowaInstance;

it('returns correct model from config', function () {
    expect(GowaInstanceResource::getModel())->toBe(GowaInstance::class);
});

it('can query instances table', function () {
    GowaInstance::create([
        'device_id'    => 'device_01',
        'name'         => 'Sales WhatsApp',
        'phone_number' => '5511999999999',
        'status'       => 'open',
    ]);

    expect(GowaInstance::count())->toBe(1);
    expect(GowaInstance::first()->device_id)->toBe('device_01');
});
