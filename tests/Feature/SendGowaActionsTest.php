<?php

declare(strict_types=1);

use Gowa\Filament\Actions\SendGowaDocumentAction;
use Gowa\Filament\Actions\SendGowaMediaAction;
use Gowa\Filament\Actions\SendGowaMessageAction;
use Gowa\Filament\Actions\SendGowaNotificationAction;
use Gowa\Laravel\Facades\Gowa;
use Gowa\Sdk\Dto\MediaPayload;
use Gowa\Sdk\Dto\MediaType;
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
        ->withArgs(fn($deviceId, $to, $text) => $deviceId === 'device_test_01' && $to === '5511999999999' && $text === 'Hello WhatsApp Test!')
        ->once()
        ->andReturn(new SentMessage(providerMessageId: 'msg_01', raw: []));

    Gowa::swap($client);

    $dummyRecord = new class () extends Model {
        protected $attributes = [
            'id'           => 1,
            'device_id'    => 'device_test_01',
            'phone_number' => '5511999999999',
        ];
    };

    $action = SendGowaMessageAction::make()
        ->instanceFromRecord()
        ->numberFrom('phone_number')
        ->message('Hello WhatsApp Test!');

    $action->executeSendMessage(['to' => '5511999999999', 'message' => 'Hello WhatsApp Test!'], $dummyRecord);
});

it('instantiates SendGowaDocumentAction and SendGowaMediaAction', function () {
    $docAction = SendGowaDocumentAction::make();
    expect($docAction->getName())->toBe('sendGowaDocument');

    $mediaAction = SendGowaMediaAction::make();
    expect($mediaAction->getName())->toBe('sendGowaMedia');
});

it('executes send document action via URL', function () {
    $client = Mockery::mock(GowaClient::class);
    $client->shouldReceive('sendMedia')
        ->withArgs(function ($deviceId, $to, MediaPayload $payload) {
            return $deviceId === 'device_test_01' && $to === '5511999999999' && $payload->type === MediaType::Document;
        })
        ->once()
        ->andReturn(new SentMessage(providerMessageId: 'msg_doc_01', raw: []));

    Gowa::swap($client);

    $dummyRecord = new class () extends Model {
        protected $attributes = [
            'id'           => 1,
            'device_id'    => 'device_test_01',
            'phone_number' => '5511999999999',
        ];
    };

    $action = SendGowaDocumentAction::make()
        ->instanceFromRecord()
        ->numberFrom('phone_number')
        ->documentUrl('https://example.com/invoice.pdf')
        ->filename('fatura.pdf');

    $action->executeSendDocument([
        'to'           => '5511999999999',
        'document_url' => 'https://example.com/invoice.pdf',
        'filename'     => 'fatura.pdf',
    ], $dummyRecord);
});

it('executes send media action via URL', function () {
    $client = Mockery::mock(GowaClient::class);
    $client->shouldReceive('sendMedia')
        ->withArgs(function ($deviceId, $to, MediaPayload $payload) {
            return $deviceId === 'device_test_01' && $to === '5511999999999' && $payload->type === MediaType::Image && $payload->caption === 'Receipt Photo';
        })
        ->once()
        ->andReturn(new SentMessage(providerMessageId: 'msg_img_01', raw: []));

    Gowa::swap($client);

    $dummyRecord = new class () extends Model {
        protected $attributes = [
            'id'           => 1,
            'device_id'    => 'device_test_01',
            'phone_number' => '5511999999999',
        ];
    };

    $action = SendGowaMediaAction::make()
        ->type(MediaType::Image)
        ->instanceFromRecord()
        ->numberFrom('phone_number')
        ->mediaUrl('https://example.com/photo.jpg')
        ->caption('Receipt Photo');

    $action->executeSendMedia([
        'to'        => '5511999999999',
        'media_url' => 'https://example.com/photo.jpg',
        'caption'   => 'Receipt Photo',
    ], $dummyRecord);
});

it('instantiates and executes SendGowaNotificationAction', function () {
    $client = Mockery::mock(GowaClient::class);
    $client->shouldReceive('sendText')
        ->with('device_test_01', '5511999999999', 'Notification Test Message', null)
        ->once()
        ->andReturn(new SentMessage(providerMessageId: 'msg_notif_01', raw: []));

    Gowa::swap($client);

    $dummyRecord = new class () extends Model {
        protected $attributes = [
            'id'           => 1,
            'device_id'    => 'device_test_01',
            'phone_number' => '5511999999999',
        ];
    };

    $action = SendGowaNotificationAction::make()
        ->instanceFromRecord()
        ->numberFrom('phone_number')
        ->message('Notification Test Message');

    expect($action->getName())->toBe('sendGowaNotification');

    $action->executeSendNotification(['to' => '5511999999999', 'message' => 'Notification Test Message'], $dummyRecord);
});
