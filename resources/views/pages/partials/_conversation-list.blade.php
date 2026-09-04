<div class="gowa-sidebar-header">
    <div class="gowa-sidebar-title">
        <h3>
            <x-filament::icon icon="heroicon-o-chat-bubble-left-right" class="h-5 w-5 text-emerald-600 dark:text-emerald-400" />
            <span>{{ __('gowa-filament::gowa-filament.conversations.title') }}</span>
        </h3>

        @if($this->unreadTotalCount > 0)
            <span class="gowa-badge-unread">
                {{ $this->unreadTotalCount }}
            </span>
        @endif
    </div>

    @if($this->instances && $this->instances->count() > 1)
        <div class="gowa-sidebar-instance-select">
            {{ $this->form }}
        </div>
    @endif

    <div class="gowa-sidebar-search">
        <x-filament::input.wrapper prefix-icon="heroicon-m-magnifying-glass">
            <x-filament::input
                type="search"
                wire:model.live.debounce.300ms="search"
                placeholder="{{ __('gowa-filament::gowa-filament.conversations.search_placeholder') }}"
            />
        </x-filament::input.wrapper>
    </div>

    <!-- Filter Pills (All / Unread) -->
    <div class="gowa-sidebar-tabs">
        <button
            type="button"
            wire:click="$set('filterUnread', false)"
            class="gowa-tab-btn {{ ! $filterUnread ? 'is-active' : '' }}"
        >
            {{ __('gowa-filament::gowa-filament.conversations.all') }}
        </button>
        <button
            type="button"
            wire:click="$set('filterUnread', true)"
            class="gowa-tab-btn {{ $filterUnread ? 'is-active' : '' }}"
        >
            <span>{{ __('gowa-filament::gowa-filament.conversations.unread') }}</span>
            @if($this->unreadTotalCount > 0)
                <span class="gowa-badge-unread" style="height: 1rem; min-width: 1rem; font-size: 0.625rem;">
                    {{ $this->unreadTotalCount }}
                </span>
            @endif
        </button>
    </div>
</div>

<div class="gowa-conv-list" wire:poll.5s>
    @forelse($this->conversations as $conversation)
        @php
            $unreadCount = $conversation->unread_count ?? 0;
            $contactName = $conversation->contact_name ?: $conversation->contact_phone;
            $initials = \Gowa\Filament\Pages\GowaConversationsPage::contactInitials($conversation->contact_name, $conversation->contact_phone);
            $avatarPalette = \Gowa\Filament\Pages\GowaConversationsPage::avatarBgColor($conversation->contact_phone ?: $conversation->contact_jid);
            $isActive = $selectedConversationId === $conversation->id;
            $latestMessage = $conversation->messages->first();
        @endphp
        <div
            wire:click="selectConversation({{ $conversation->id }})"
            wire:key="conv-{{ $conversation->id }}"
            class="gowa-conv-item {{ $isActive ? 'is-active' : '' }}"
        >
            <div class="gowa-avatar {{ $avatarPalette }}">
                {{ $initials }}
            </div>

            <div class="gowa-conv-details">
                <div class="gowa-conv-top-line">
                    <span class="gowa-conv-name">
                        {{ $contactName }}
                    </span>
                    @if($conversation->last_message_at)
                        <span class="gowa-conv-time">
                            {{ $conversation->last_message_at->diffForHumans(short: true) }}
                        </span>
                    @endif
                </div>

                <div class="gowa-conv-bottom-line">
                    <p class="gowa-conv-preview">
                        @if($latestMessage)
                            @if(! $latestMessage->isInbound())
                                <span style="display: inline-flex; align-items: center; vertical-align: middle; margin-right: 0.2rem;">
                                    @if($latestMessage->status === \Gowa\Laravel\Enums\GowaMessageStatus::Read)
                                        <span style="display: inline-flex; align-items: center; color: #53bdeb;" title="Read">
                                            <x-filament::icon icon="heroicon-m-check" style="width: 0.75rem; height: 0.75rem;" />
                                            <x-filament::icon icon="heroicon-m-check" style="width: 0.75rem; height: 0.75rem; margin-left: -0.4rem;" />
                                        </span>
                                    @elseif($latestMessage->status === \Gowa\Laravel\Enums\GowaMessageStatus::Delivered)
                                        <span style="display: inline-flex; align-items: center; opacity: 0.65;" title="Delivered">
                                            <x-filament::icon icon="heroicon-m-check" style="width: 0.75rem; height: 0.75rem;" />
                                            <x-filament::icon icon="heroicon-m-check" style="width: 0.75rem; height: 0.75rem; margin-left: -0.4rem;" />
                                        </span>
                                    @elseif($latestMessage->status === \Gowa\Laravel\Enums\GowaMessageStatus::Sent)
                                        <span style="display: inline-flex; align-items: center; opacity: 0.65;" title="Sent">
                                            <x-filament::icon icon="heroicon-m-check" style="width: 0.75rem; height: 0.75rem;" />
                                        </span>
                                    @elseif($latestMessage->status === \Gowa\Laravel\Enums\GowaMessageStatus::Failed)
                                        <x-filament::icon icon="heroicon-m-exclamation-circle" style="width: 0.75rem; height: 0.75rem;" class="text-rose-500 inline" />
                                    @else
                                        <x-filament::icon icon="heroicon-m-clock" style="width: 0.75rem; height: 0.75rem;" class="inline opacity-70" />
                                    @endif
                                </span>
                            @endif

                            @if($latestMessage->type !== 'text')
                                <span>
                                    @switch($latestMessage->type)
                                        @case('image') 📷 @break
                                        @case('video') 🎥 @break
                                        @case('audio') 🎙️ @break
                                        @case('document') 📄 @break
                                        @case('location') 📍 @break
                                        @case('contact') 👤 @break
                                        @case('sticker') 🏷️ @break
                                        @case('poll') 📊 @break
                                    @endswitch
                                </span>
                            @endif

                            <span>
                                {{ Str::limit($latestMessage->body ?: \Gowa\Filament\Pages\GowaConversationsPage::messageTypeLabel($latestMessage->type), 35) }}
                            </span>
                        @else
                            <span style="font-style: italic; opacity: 0.7;">{{ __('gowa-filament::gowa-filament.conversations.no_messages') }}</span>
                        @endif
                    </p>

                    @if($unreadCount > 0)
                        <span class="gowa-badge-unread">
                            {{ $unreadCount }}
                        </span>
                    @endif
                </div>
            </div>
        </div>
    @empty
        <div class="gowa-empty-state">
            <x-filament::icon icon="heroicon-o-chat-bubble-left-right" class="h-10 w-10 text-gray-400 opacity-40" />
            @if(! empty($search))
                <p style="font-size: 0.875rem; font-weight: 500; color: var(--gowa-text-muted); margin: 0;">
                    {{ __('gowa-filament::gowa-filament.conversations.no_search_results') }}
                </p>
                <button
                    type="button"
                    wire:click="$set('search', '')"
                    style="font-size: 0.75rem; color: var(--gowa-active-border); font-weight: 600; cursor: pointer; background: none; border: none; text-decoration: underline;"
                >
                    {{ __('gowa-filament::gowa-filament.conversations.clear_search') }}
                </button>
            @elseif($filterUnread)
                <p style="font-size: 0.875rem; font-weight: 500; color: var(--gowa-text-muted); margin: 0;">
                    {{ __('gowa-filament::gowa-filament.conversations.no_unread') }}
                </p>
            @else
                <p style="font-size: 0.875rem; font-weight: 500; color: var(--gowa-text-muted); margin: 0;">
                    {{ __('gowa-filament::gowa-filament.conversations.no_conversations') }}
                </p>
            @endif
        </div>
    @endforelse
</div>
