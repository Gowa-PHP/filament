<?php

use Gowa\Filament\Livewire\GowaQrCode;
use Gowa\Sdk\Dto\Device;
use Gowa\Sdk\Dto\Pairing;
use Gowa\Sdk\GowaClient;
use Livewire\Livewire;

it('mounts and loads qr code url', function () {
    $client = Mockery::mock(GowaClient::class);
    $client->shouldReceive('startQrPairing')
        ->with('device_01')
        ->andReturn(new Pairing(qrLink: 'https://gowa-api.test/qr.png'));

    $client->shouldReceive('device')
        ->with('device_01')
        ->andReturn(new Device(deviceId: 'device_01', name: 'Sales WhatsApp', status: 'connecting'));

    app()->instance(GowaClient::class, $client);

    Livewire::test(GowaQrCode::class, ['deviceId' => 'device_01'])
        ->assertSet('deviceId', 'device_01')
        ->assertSet('qrCodeUrl', 'https://gowa-api.test/qr.png')
        ->assertSee('gowa-api.test/qr.png');
});

it('dispatches connected event when status becomes open', function () {
    $client = Mockery::mock(GowaClient::class);
    $client->shouldReceive('startQrPairing')
        ->with('device_01')
        ->andReturn(new Pairing(qrLink: 'https://gowa-api.test/qr.png'));

    $client->shouldReceive('device')
        ->with('device_01')
        ->andReturn(new Device(deviceId: 'device_01', name: 'Sales WhatsApp', status: 'open'));

    app()->instance(GowaClient::class, $client);

    Livewire::test(GowaQrCode::class, ['deviceId' => 'device_01'])
        ->assertSet('status', 'connected');
});

it('expires qr code after timeout and stops polling', function () {
    $client = Mockery::mock(GowaClient::class);
    $client->shouldReceive('startQrPairing')
        ->with('device_01')
        ->andReturn(new Pairing(qrLink: 'https://gowa-api.test/qr.png'));

    $client->shouldReceive('device')
        ->with('device_01')
        ->andReturn(new Device(deviceId: 'device_01', name: 'Sales WhatsApp', status: 'connecting'));

    app()->instance(GowaClient::class, $client);

    $component = Livewire::test(GowaQrCode::class, ['deviceId' => 'device_01']);
    $component->set('expiresAtTimestamp', time() - 1);
    $component->call('checkStatus');

    $component->assertSet('isExpired', true)
        ->assertSet('qrCodeUrl', null);
});
