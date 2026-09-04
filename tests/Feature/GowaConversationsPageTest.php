<?php

declare(strict_types=1);

use Gowa\Filament\GowaPlugin;
use Gowa\Filament\Pages\GowaConversationsPage;
use Gowa\Laravel\Models\GowaConversation;
use Gowa\Laravel\Models\GowaInstance;
use Gowa\Laravel\Models\GowaMessage;

use function Pest\Livewire\livewire;

beforeEach(function () {
    $this->instance = GowaInstance::create([
        'device_id' => 'test-device-inbox',
        'name'      => 'Test Instance',
        'status'    => 'open',
    ]);

    $this->conversation = GowaConversation::create([
        'instance_id'     => $this->instance->id,
        'contact_jid'     => '5511999999999@s.whatsapp.net',
        'contact_name'    => 'João Silva',
        'contact_phone'   => '5511999999999',
        'last_message_at' => now(),
    ]);
});

it('can render conversations page', function () {
    livewire(GowaConversationsPage::class)
        ->assertSuccessful();
});

it('shows conversation list with contacts', function () {
    livewire(GowaConversationsPage::class)
        ->assertSee('João Silva');
});

it('can select a conversation', function () {
    livewire(GowaConversationsPage::class)
        ->call('selectConversation', $this->conversation->id)
        ->assertSet('selectedConversationId', $this->conversation->id);
});

it('shows messages for selected conversation', function () {
    GowaMessage::create([
        'instance_id'     => $this->instance->id,
        'conversation_id' => $this->conversation->id,
        'message_id'      => 'MSG001',
        'direction'       => 'inbound',
        'status'          => 'delivered',
        'type'            => 'text',
        'body'            => 'Hello from WhatsApp!',
        'sent_at'         => now(),
    ]);

    livewire(GowaConversationsPage::class)
        ->call('selectConversation', $this->conversation->id)
        ->assertSee('Hello from WhatsApp!');
});

it('can search conversations by name', function () {
    GowaConversation::create([
        'instance_id'     => $this->instance->id,
        'contact_jid'     => '5511888888888@s.whatsapp.net',
        'contact_name'    => 'Maria Oliveira',
        'contact_phone'   => '5511888888888',
        'last_message_at' => now()->subDay(),
    ]);

    livewire(GowaConversationsPage::class)
        ->set('search', 'Maria')
        ->assertSee('Maria Oliveira')
        ->assertDontSee('João Silva');
});

it('can filter unread conversations', function () {
    // Create an unread inbound message for the existing conversation
    GowaMessage::create([
        'instance_id'     => $this->instance->id,
        'conversation_id' => $this->conversation->id,
        'message_id'      => 'MSG002',
        'direction'       => 'inbound',
        'status'          => 'delivered',
        'type'            => 'text',
        'body'            => 'Unread message',
        'sent_at'         => now(),
        'read_at'         => null,
    ]);

    // Create another conversation with no unread messages
    $readConversation = GowaConversation::create([
        'instance_id'     => $this->instance->id,
        'contact_jid'     => '5511777777777@s.whatsapp.net',
        'contact_name'    => 'Pedro Santos',
        'contact_phone'   => '5511777777777',
        'last_message_at' => now()->subHours(2),
    ]);

    GowaMessage::create([
        'instance_id'     => $this->instance->id,
        'conversation_id' => $readConversation->id,
        'message_id'      => 'MSG003',
        'direction'       => 'inbound',
        'status'          => 'read',
        'type'            => 'text',
        'body'            => 'Already read message',
        'sent_at'         => now()->subHours(2),
        'read_at'         => now()->subHour(),
    ]);

    livewire(GowaConversationsPage::class)
        ->set('filterUnread', true)
        ->assertSee('João Silva')
        ->assertDontSee('Pedro Santos');
});

it('shows empty state when no conversations', function () {
    GowaConversation::query()->delete();

    livewire(GowaConversationsPage::class)
        ->assertSee(__('gowa-filament::gowa-filament.conversations.no_conversations'));
});

it('conversations page is toggleable via plugin', function () {
    $plugin = GowaPlugin::make()->conversationsPage(false);

    expect($plugin->hasConversationsPage())->toBeFalse();

    $plugin->conversationsPage(true);

    expect($plugin->hasConversationsPage())->toBeTrue();
});

it('displays different message types', function () {
    $types = [
        ['type' => 'text', 'body' => 'Simple text message'],
        ['type' => 'image', 'body' => 'Photo caption'],
        ['type' => 'document', 'body' => 'report.pdf'],
        ['type' => 'location', 'body' => '-23.550520, -46.633308'],
    ];

    foreach ($types as $i => $data) {
        GowaMessage::create([
            'instance_id'     => $this->instance->id,
            'conversation_id' => $this->conversation->id,
            'message_id'      => "MSG-TYPE-{$i}",
            'direction'       => 'inbound',
            'status'          => 'delivered',
            'type'            => $data['type'],
            'body'            => $data['body'],
            'sent_at'         => now()->addSeconds($i),
        ]);
    }

    livewire(GowaConversationsPage::class)
        ->call('selectConversation', $this->conversation->id)
        ->assertSee('Simple text message')
        ->assertSee('Photo caption')
        ->assertSee('report.pdf')
        ->assertSee('-23.550520, -46.633308');
});

it('can send a text message in active conversation', function () {
    $client = Mockery::mock(\Gowa\Sdk\GowaClient::class);
    $client->shouldReceive('sendText')
        ->with('test-device-inbox', '5511999999999@s.whatsapp.net', 'Hello there!')
        ->once()
        ->andReturn(new \Gowa\Sdk\Dto\SentMessage(providerMessageId: 'sent_01', raw: []));

    \Gowa\Laravel\Facades\Gowa::swap($client);

    livewire(GowaConversationsPage::class)
        ->call('selectConversation', $this->conversation->id)
        ->set('newMessage', 'Hello there!')
        ->call('sendMessage')
        ->assertSet('newMessage', '')
        ->assertNotified(__('gowa-filament::gowa-filament.notifications.message_sent'));
});

it('can mark conversation messages as read', function () {
    $message = GowaMessage::create([
        'instance_id'     => $this->instance->id,
        'conversation_id' => $this->conversation->id,
        'message_id'      => 'MSG_UNREAD_01',
        'direction'       => 'inbound',
        'status'          => 'delivered',
        'type'            => 'text',
        'body'            => 'Unread text',
        'sent_at'         => now(),
        'read_at'         => null,
    ]);

    $client = Mockery::mock(\Gowa\Sdk\GowaClient::class);
    $client->shouldReceive('markRead')
        ->with('test-device-inbox', '5511999999999@s.whatsapp.net', 'MSG_UNREAD_01')
        ->once();

    \Gowa\Laravel\Facades\Gowa::swap($client);

    livewire(GowaConversationsPage::class)
        ->call('selectConversation', $this->conversation->id)
        ->call('markConversationRead', $this->conversation->id)
        ->assertNotified();

    expect($message->fresh()->read_at)->not->toBeNull()
        ->and($message->fresh()->status)->toBe(\Gowa\Laravel\Enums\GowaMessageStatus::Read);
});

it('records outbound message in local database when sending text', function () {
    $client = Mockery::mock(\Gowa\Sdk\GowaClient::class);
    $client->shouldReceive('sendText')
        ->with('test-device-inbox', '5511999999999@s.whatsapp.net', 'Recorded outbound message')
        ->once()
        ->andReturn(new \Gowa\Sdk\Dto\SentMessage(providerMessageId: 'prov_msg_99', raw: []));

    \Gowa\Laravel\Facades\Gowa::swap($client);

    livewire(GowaConversationsPage::class)
        ->call('selectConversation', $this->conversation->id)
        ->set('newMessage', 'Recorded outbound message')
        ->call('sendMessage');

    $saved = GowaMessage::where('message_id', 'prov_msg_99')->first();
    expect($saved)->not->toBeNull()
        ->and($saved->body)->toBe('Recorded outbound message')
        ->and($saved->direction)->toBe(\Gowa\Laravel\Enums\GowaMessageDirection::Outbound)
        ->and($saved->status)->toBe(\Gowa\Laravel\Enums\GowaMessageStatus::Sent);
});

it('resets active conversation when selected instance changes', function () {
    $otherInstance = GowaInstance::create([
        'device_id' => 'other-device',
        'name'      => 'Other Device',
        'status'    => 'open',
    ]);

    livewire(GowaConversationsPage::class)
        ->call('selectConversation', $this->conversation->id)
        ->assertSet('selectedConversationId', $this->conversation->id)
        ->set('selectedInstanceId', $otherInstance->device_id)
        ->assertSet('selectedConversationId', null);
});

it('renders media preview and download link', function () {
    GowaMessage::create([
        'instance_id'     => $this->instance->id,
        'conversation_id' => $this->conversation->id,
        'message_id'      => 'MSG_MEDIA_PREVIEW',
        'direction'       => 'inbound',
        'status'          => 'delivered',
        'type'            => 'image',
        'body'            => 'A lovely picture',
        'media_url'       => 'https://example.com/photos/avatar.jpg',
        'sent_at'         => now(),
    ]);

    livewire(GowaConversationsPage::class)
        ->call('selectConversation', $this->conversation->id)
        ->assertSeeHtml('https://example.com/photos/avatar.jpg')
        ->assertSee('A lovely picture');
});

it('automatically marks inbound messages as read when opening conversation', function () {
    $message = GowaMessage::create([
        'instance_id'     => $this->instance->id,
        'conversation_id' => $this->conversation->id,
        'message_id'      => 'MSG_AUTO_READ_01',
        'direction'       => 'inbound',
        'status'          => 'delivered',
        'type'            => 'text',
        'body'            => 'Unread message before open',
        'sent_at'         => now(),
        'read_at'         => null,
    ]);

    expect($message->read_at)->toBeNull();

    livewire(GowaConversationsPage::class)
        ->call('selectConversation', $this->conversation->id);

    expect($message->fresh()->read_at)->not->toBeNull()
        ->and($message->fresh()->status)->toBe(\Gowa\Laravel\Enums\GowaMessageStatus::Read);
});
