<?php

declare(strict_types=1);

use Gowa\Filament\Livewire\GowaPairingCode;
use Gowa\Sdk\Dto\Device;
use Gowa\Sdk\Dto\Pairing;
use Gowa\Sdk\GowaClient;
use Livewire\Livewire;

it('validates phone number input when generating code', function () {
    $client = Mockery::mock(GowaClient::class);
    $client->shouldReceive('device')
        ->with('device_01')
        ->andReturn(new Device(deviceId: 'device_01', name: 'Sales WhatsApp', status: 'close'));

    app()->instance(GowaClient::class, $client);

    $testable = Livewire::test(GowaPairingCode::class, ['deviceId' => 'device_01'])
        ->set('phone', '123')
        ->call('generateCode');

    expect($testable->get('errorMessage'))->not->toBeNull();
});

it('generates pairing code successfully', function () {
    $client = Mockery::mock(GowaClient::class);
    $client->shouldReceive('device')
        ->with('device_01')
        ->andReturn(new Device(deviceId: 'device_01', name: 'Sales WhatsApp', status: 'close'));

    $client->shouldReceive('startCodePairing')
        ->with('device_01', '5511999999999')
        ->once()
        ->andReturn(new Pairing(pairCode: '12345678'));

    app()->instance(GowaClient::class, $client);

    Livewire::test(GowaPairingCode::class, ['deviceId' => 'device_01'])
        ->set('phone', '5511999999999')
        ->call('generateCode')
        ->assertSet('pairingCode', '12345678')
        ->assertSet('status', 'connecting')
        ->assertSee('1234-5678');
});
