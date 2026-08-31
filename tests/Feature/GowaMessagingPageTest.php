<?php

use Gowa\Filament\Pages\GowaMessagingPage;
use Gowa\Laravel\Facades\Gowa;
use Gowa\Laravel\Models\GowaInstance;
use Gowa\Sdk\Dto\SentMessage;
use Gowa\Sdk\GowaClient;
use Livewire\Livewire;

it('mounts GowaMessagingPage successfully', function () {
    Livewire::test(GowaMessagingPage::class)
        ->assertSet('data.recipient_type', 'private')
        ->assertSet('data.message_type', 'text');
});

it('sends text message from GowaMessagingPage', function () {
    GowaInstance::create([
        'name' => 'Instance 01',
        'device_id' => 'device_01',
        'status' => 'open',
    ]);

    $client = Mockery::mock(GowaClient::class);
    $client->shouldReceive('sendText')
        ->with('device_01', '5511999999999', 'Test message from page', null)
        ->once()
        ->andReturn(new SentMessage(providerMessageId: 'msg_page_01', raw: []));

    Gowa::swap($client);

    Livewire::test(GowaMessagingPage::class)
        ->set('data.device_id', 'device_01')
        ->set('data.to', '5511999999999')
        ->set('data.message_type', 'text')
        ->set('data.message', 'Test message from page')
        ->call('send');
});
