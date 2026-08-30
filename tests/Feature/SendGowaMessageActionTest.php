<?php

use Gowa\Filament\Actions\SendGowaMessageAction;
use Gowa\Laravel\Facades\Gowa;
use Gowa\Sdk\Dto\SentMessage;
use Gowa\Sdk\GowaClient;
use Illuminate\Database\Eloquent\Model;

it('instantiates SendGowaMessageAction with default settings', function () {
    $action = SendGowaMessageAction::make();

    expect($action->getName())->toBe('sendGowaMessage')
        ->and($action->getLabel())->toBe(__('gowa-filament::gowa-filament.actions.send_message'));
});

it('executes send text message via action', function () {
    $client = Mockery::mock(GowaClient::class);
    $client->shouldReceive('sendText')
        ->with('device_test_01', '5511999999999', 'Hello WhatsApp Test!')
        ->once()
        ->andReturn(new SentMessage(providerMessageId: 'msg_01', raw: []));

    Gowa::swap($client);

    $dummyRecord = new class extends Model {
        protected $attributes = [
            'id' => 1,
            'device_id' => 'device_test_01',
            'phone_number' => '5511999999999',
        ];
    };

    $action = SendGowaMessageAction::make()
        ->instanceFromRecord()
        ->numberFrom('phone_number')
        ->message('Hello WhatsApp Test!');

    $action->executeSendMessage(['to' => '5511999999999', 'message' => 'Hello WhatsApp Test!'], $dummyRecord);
});
