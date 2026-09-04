<?php

declare(strict_types=1);

use Gowa\Filament\Actions\SendGowaAction;
use Gowa\Laravel\Facades\Gowa;
use Gowa\Laravel\PendingMessage;
use Gowa\Sdk\Dto\MediaPayload;
use Gowa\Sdk\Dto\MediaType;
use Gowa\Sdk\Dto\SentMessage;
use Gowa\Sdk\GowaClient;
use Illuminate\Database\Eloquent\Model;

it('instantiates SendGowaAction with default settings and fluent chaining', function () {
    $action = SendGowaAction::make()
        ->to('5511999999999')
        ->from('device_test_01')
        ->text('Hello World')
        ->direct();

    expect($action->getName())->toBe('sendGowa')
        ->and($action->getLabel())->toBe(__('gowa-filament::gowa-filament.actions.send_message'));
});

it('executes direct text send with fluent API', function () {
    $client = Mockery::mock(GowaClient::class);
    $client->shouldReceive('sendText')
        ->withArgs(fn($deviceId, $to, $text) => $deviceId === 'device_test_01' && $to === '5511999999999' && $text === 'Hello Fluent!')
        ->once()
        ->andReturn(new SentMessage(providerMessageId: 'msg_fluent_01', raw: []));

    Gowa::swap($client);

    $dummyRecord = new class () extends Model {
        protected $attributes = [
            'id'           => 1,
            'device_id'    => 'device_test_01',
            'phone_number' => '5511999999999',
        ];
    };

    $action = SendGowaAction::make()
        ->instanceFromRecord()
        ->to(fn($record) => $record->phone_number)
        ->text('Hello Fluent!')
        ->direct();

    $action->executeSend([], $dummyRecord);
});

it('executes document send with fluent API', function () {
    $client = Mockery::mock(GowaClient::class);
    $client->shouldReceive('sendMedia')
        ->withArgs(fn($deviceId, $to, MediaPayload $payload) => $deviceId === 'device_test_01' && $to === '5511999999999' && $payload->type === MediaType::Document)
        ->once()
        ->andReturn(new SentMessage(providerMessageId: 'msg_doc_fluent_01', raw: []));

    Gowa::swap($client);

    $action = SendGowaAction::make()
        ->from('device_test_01')
        ->to('5511999999999')
        ->document('https://example.com/invoice.pdf', filename: 'invoice.pdf')
        ->direct();

    $action->executeSend([], null);
});

it('executes image send with fluent API', function () {
    $client = Mockery::mock(GowaClient::class);
    $client->shouldReceive('sendMedia')
        ->withArgs(fn($deviceId, $to, MediaPayload $payload) => $deviceId === 'device_test_01' && $to === '5511999999999' && $payload->type === MediaType::Image && $payload->caption === 'Nice photo')
        ->once()
        ->andReturn(new SentMessage(providerMessageId: 'msg_img_fluent_01', raw: []));

    Gowa::swap($client);

    $action = SendGowaAction::make()
        ->from('device_test_01')
        ->to('5511999999999')
        ->image('https://example.com/photo.jpg', caption: 'Nice photo')
        ->direct();

    $action->executeSend([], null);
});

it('executes custom fluent callback', function () {
    $client = Mockery::mock(GowaClient::class);
    $client->shouldReceive('sendPoll')
        ->withArgs(fn($deviceId, $to, $question, $options) => $deviceId === 'device_test_01' && $to === '5511999999999' && $question === 'Feedback?')
        ->once()
        ->andReturn(new SentMessage(providerMessageId: 'msg_poll_01', raw: []));

    Gowa::swap($client);

    $action = SendGowaAction::make()
        ->from('device_test_01')
        ->to('5511999999999')
        ->fluent(function (PendingMessage $msg, $record) {
            return $msg->poll('Feedback?', ['Otimo', 'Bom']);
        });

    $action->executeSend([], null);
});

it('builds form schema for modal interaction', function () {
    $action = SendGowaAction::make()
        ->from('device_test_01')
        ->to('5511999999999')
        ->text('Default message');

    $schema = $action->getFormSchema(null);
    expect($schema)->toBeArray()
        ->and(count($schema))->toBeGreaterThan(0);
});

it('executes video send with fluent API', function () {
    $client = Mockery::mock(GowaClient::class);
    $client->shouldReceive('sendMedia')
        ->withArgs(fn($deviceId, $to, MediaPayload $payload) => $deviceId === 'device_test_01' && $to === '5511999999999' && $payload->type === MediaType::Video && $payload->caption === 'Video sample')
        ->once()
        ->andReturn(new SentMessage(providerMessageId: 'msg_vid_01', raw: []));

    Gowa::swap($client);

    $action = SendGowaAction::make()
        ->from('device_test_01')
        ->to('5511999999999')
        ->video('https://example.com/video.mp4', caption: 'Video sample')
        ->direct();

    $action->executeSend([], null);
});

it('executes audio and voice send with fluent API', function () {
    $client = Mockery::mock(GowaClient::class);
    $client->shouldReceive('sendMedia')
        ->withArgs(fn($deviceId, $to, MediaPayload $payload) => $deviceId === 'device_test_01' && $to === '5511999999999' && $payload->type === MediaType::Audio && ! $payload->voice)
        ->once()
        ->andReturn(new SentMessage(providerMessageId: 'msg_aud_01', raw: []));

    $client->shouldReceive('sendMedia')
        ->withArgs(fn($deviceId, $to, MediaPayload $payload) => $deviceId === 'device_test_01' && $to === '5511999999999' && $payload->type === MediaType::Audio && $payload->voice)
        ->once()
        ->andReturn(new SentMessage(providerMessageId: 'msg_vox_01', raw: []));

    Gowa::swap($client);

    $audioAction = SendGowaAction::make()
        ->from('device_test_01')
        ->to('5511999999999')
        ->audio('https://example.com/sound.mp3')
        ->direct();

    $audioAction->executeSend([], null);

    $voiceAction = SendGowaAction::make()
        ->from('device_test_01')
        ->to('5511999999999')
        ->voice('https://example.com/voice.ogg')
        ->direct();

    $voiceAction->executeSend([], null);
});

it('executes location send with fluent API', function () {
    $client = Mockery::mock(GowaClient::class);
    $client->shouldReceive('sendLocation')
        ->withArgs(fn($deviceId, $to, $loc) => $deviceId === 'device_test_01' && $to === '5511999999999' && $loc->latitude === -23.55052 && $loc->longitude === -46.633308)
        ->once()
        ->andReturn(new SentMessage(providerMessageId: 'msg_loc_01', raw: []));

    Gowa::swap($client);

    $action = SendGowaAction::make()
        ->from('device_test_01')
        ->to('5511999999999')
        ->location(-23.55052, -46.633308)
        ->direct();

    $action->executeSend([], null);
});

it('executes contact send with fluent API', function () {
    $client = Mockery::mock(GowaClient::class);
    $client->shouldReceive('sendContacts')
        ->withArgs(fn($deviceId, $to, $contacts) => $deviceId === 'device_test_01' && $to === '5511999999999' && $contacts[0]->name === 'João Silva')
        ->once()
        ->andReturn(new SentMessage(providerMessageId: 'msg_contact_01', raw: []));

    Gowa::swap($client);

    $action = SendGowaAction::make()
        ->from('device_test_01')
        ->to('5511999999999')
        ->contact('João Silva', '+5511999999999')
        ->direct();

    $action->executeSend([], null);
});

it('executes poll send with fluent API', function () {
    $client = Mockery::mock(GowaClient::class);
    $client->shouldReceive('sendPoll')
        ->withArgs(fn($deviceId, $to, $question, $options, $maxSelections) => $deviceId === 'device_test_01' && $to === '5511999999999' && $question === 'Escolha uma cor' && count($options) === 2 && $maxSelections === 1)
        ->once()
        ->andReturn(new SentMessage(providerMessageId: 'msg_poll_02', raw: []));

    Gowa::swap($client);

    $action = SendGowaAction::make()
        ->from('device_test_01')
        ->to('5511999999999')
        ->poll('Escolha uma cor', ['Azul', 'Verde'])
        ->direct();

    $action->executeSend([], null);
});

it('executes whatsapp link send with fluent API', function () {
    $client = Mockery::mock(GowaClient::class);
    $client->shouldReceive('sendLink')
        ->withArgs(fn($deviceId, $to, $url, $caption) => $deviceId === 'device_test_01' && $to === '5511999999999' && $url === 'https://filamentphp.com' && $caption === 'Filament Website')
        ->once()
        ->andReturn(new SentMessage(providerMessageId: 'msg_link_01', raw: []));

    Gowa::swap($client);

    $action = SendGowaAction::make()
        ->from('device_test_01')
        ->to('5511999999999')
        ->whatsappLink('https://filamentphp.com', 'Filament Website')
        ->direct();

    $action->executeSend([], null);
});

it('supports replyTo and disk chaining in SendGowaAction', function () {
    $client = Mockery::mock(GowaClient::class);
    $client->shouldReceive('sendText')
        ->withArgs(fn($deviceId, $to, $text, $replyTo) => $deviceId === 'device_test_01' && $to === '5511999999999' && $text === 'Replying!' && $replyTo === 'parent_msg_123')
        ->once()
        ->andReturn(new SentMessage(providerMessageId: 'msg_rep_01', raw: []));

    Gowa::swap($client);

    $action = SendGowaAction::make()
        ->from('device_test_01')
        ->to('5511999999999')
        ->replyTo('parent_msg_123')
        ->text('Replying!')
        ->disk('public')
        ->direct();

    $action->executeSend([], null);
});

it('executes Gowa facade fluent chain directly', function () {
    $client = Mockery::mock(GowaClient::class);
    $client->shouldReceive('sendText')
        ->withArgs(fn($deviceId, $to, $text) => $deviceId === 'device_test_01' && $to === '5511988888888' && $text === 'Direct Facade Call!')
        ->once()
        ->andReturn(new SentMessage(providerMessageId: 'msg_facade_01', raw: []));

    Gowa::swap($client);

    $sent = Gowa::to('5511988888888')
        ->from('device_test_01')
        ->text('Direct Facade Call!')
        ->send();

    expect($sent)->toBeInstanceOf(SentMessage::class)
        ->and($sent->providerMessageId)->toBe('msg_facade_01');
});

it('executes send using form data submitted from modal', function () {
    $client = Mockery::mock(GowaClient::class);
    $client->shouldReceive('sendText')
        ->withArgs(fn($deviceId, $to, $text) => $deviceId === 'device_modal_01' && $to === '5511777777777' && $text === 'Modal edited text')
        ->once()
        ->andReturn(new SentMessage(providerMessageId: 'msg_modal_01', raw: []));

    Gowa::swap($client);

    $action = SendGowaAction::make();
    $action->executeSend([
        'device_id' => 'device_modal_01',
        'to'        => '5511777777777',
        'message'   => 'Modal edited text',
    ], null);
});

it('falls back to first open instance when device_id not specified', function () {
    \Gowa\Laravel\Models\GowaInstance::create([
        'device_id' => 'device_auto_db',
        'name'      => 'Default Instance',
        'status'    => 'open',
    ]);

    $client = Mockery::mock(GowaClient::class);
    $client->shouldReceive('sendText')
        ->withArgs(fn($deviceId, $to, $text) => $deviceId === 'device_auto_db' && $to === '5511999999999' && $text === 'Auto DB device')
        ->once()
        ->andReturn(new SentMessage(providerMessageId: 'msg_auto_01', raw: []));

    Gowa::swap($client);

    $action = SendGowaAction::make()
        ->to('5511999999999')
        ->text('Auto DB device')
        ->direct();

    $action->executeSend([], null);
});
