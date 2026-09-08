@if(! $this->activeConversation)
    <div class="gowa-empty-state" style="background-color: var(--gowa-sidebar-bg);">
        <div style="width: 4.5rem; height: 4.5rem; border-radius: 9999px; background-color: rgba(16, 185, 129, 0.1); color: #10b981; display: flex; align-items: center; justify-content: center; margin-bottom: 1rem;">
            <x-filament::icon
                icon="heroicon-o-chat-bubble-bottom-center-text"
                class="h-9 w-9"
            />
        </div>
        <h4 style="font-size: 1rem; font-weight: 600; color: var(--gowa-text); margin: 0 0 0.25rem 0;">
            {{ __('gowa-filament::gowa-filament.conversations.title') }}
        </h4>
        <p style="font-size: 0.75rem; color: var(--gowa-text-muted); max-width: 22rem; line-height: 1.5; margin: 0 0 1.5rem 0;">
            {{ __('gowa-filament::gowa-filament.conversations.select_conversation') }}
        </p>
        <div style="display: inline-flex; align-items: center; gap: 0.5rem; padding: 0.375rem 0.875rem; border-radius: 9999px; font-size: 0.75rem; color: var(--gowa-text-muted); background-color: rgba(0, 0, 0, 0.04); border: 1px solid var(--gowa-border);">
            <x-filament::icon icon="heroicon-m-lock-closed" class="h-3.5 w-3.5 opacity-60" />
            <span>{{ __('gowa-filament::gowa-filament.conversations.secure_notice') }}</span>
        </div>
    </div>
@else
    @php
        $activeContactName = $this->activeConversation->contact_name ?: $this->activeConversation->contact_phone;
        $activeInitials = \Gowa\Filament\Pages\GowaConversationsPage::contactInitials($this->activeConversation->contact_name, $this->activeConversation->contact_phone);
        $activePalette = \Gowa\Filament\Pages\GowaConversationsPage::avatarBgColor($this->activeConversation->contact_phone ?: $this->activeConversation->contact_jid);
        $cleanPhone = preg_replace('/\D/', '', $this->activeConversation->contact_phone ?? '');
        $instance = $this->activeConversation->instance;
    @endphp

    <!-- Chat Header -->
    <div class="gowa-chat-header">
        <div style="display: flex; align-items: center; gap: 0.75rem; min-width: 0;">
            <!-- Mobile back button -->
            <button
                type="button"
                wire:click="$set('selectedConversationId', null)"
                style="padding: 0.375rem; border-radius: 0.5rem; color: var(--gowa-text-muted); background: none; border: none; cursor: pointer;"
                class="md:hidden"
            >
                <x-filament::icon icon="heroicon-m-arrow-left" class="h-5 w-5" />
            </button>

            <!-- Contact Avatar -->
            <div class="gowa-avatar {{ $activePalette }}" style="width: 2.5rem; height: 2.5rem;">
                {{ $activeInitials }}
            </div>

            <div style="min-width: 0; display: flex; flex-direction: column; gap: 0.125rem;">
                <div style="display: flex; align-items: center; gap: 0.5rem;">
                    <h4 style="font-size: 0.875rem; font-weight: 600; color: var(--gowa-text); margin: 0; overflow: hidden; text-overflow: ellipsis; white-space: nowrap;">
                        {{ $activeContactName }}
                    </h4>
                    @if($instance)
                        @php
                            $instanceAvatar = \Gowa\Filament\Pages\GowaConversationsPage::instanceAvatarUrl($instance);
                        @endphp
                        <span style="display: inline-flex; align-items: center; gap: 0.35rem; padding: 0.125rem 0.5rem 0.125rem 0.25rem; border-radius: 9999px; font-size: 0.6875rem; font-weight: 500; background-color: rgba(0, 0, 0, 0.05); color: var(--gowa-text-muted);">
                            <img
                                src="{{ $instanceAvatar }}"
                                alt="{{ $instance->name ?: $instance->device_id }}"
                                style="width: 1.125rem; height: 1.125rem; border-radius: 9999px; object-fit: cover; display: inline-block;"
                                loading="lazy"
                            />
                            <span style="overflow: hidden; text-overflow: ellipsis; white-space: nowrap; max-width: 120px;">{{ $instance->name ?: $instance->device_id }}</span>
                        </span>
                    @endif
                </div>

                @if($this->activeConversation->contact_phone)
                    <p style="font-size: 0.6875rem; color: var(--gowa-text-muted); margin: 0; overflow: hidden; text-overflow: ellipsis; white-space: nowrap;">
                        {{ $this->activeConversation->contact_phone }}
                    </p>
                @endif
            </div>
        </div>

        <div style="display: flex; align-items: center; gap: 0.5rem; flex-shrink: 0;">
            @if(! empty($cleanPhone))
                <a
                    href="https://wa.me/{{ $cleanPhone }}"
                    target="_blank"
                    rel="noopener noreferrer"
                    title="{{ __('gowa-filament::gowa-filament.conversations.open_in_whatsapp') }}"
                    style="padding: 0.375rem; border-radius: 0.375rem; color: var(--gowa-text-muted); text-decoration: none; display: inline-flex; align-items: center;"
                >
                    <x-filament::icon icon="heroicon-m-arrow-top-right-on-square" class="h-4 w-4" />
                </a>
            @endif

            <button
                type="button"
                wire:click="$refresh"
                title="{{ __('gowa-filament::gowa-filament.conversations.refresh') }}"
                style="padding: 0.375rem; border-radius: 0.375rem; color: var(--gowa-text-muted); background: none; border: none; cursor: pointer; display: inline-flex; align-items: center;"
            >
                <x-filament::icon icon="heroicon-m-arrow-path" class="h-4 w-4" />
            </button>

            <x-filament::button
                wire:click="markConversationRead({{ $this->activeConversation->id }})"
                color="gray"
                size="xs"
                icon="heroicon-m-check"
            >
                <span class="hidden sm:inline">{{ __('gowa-filament::gowa-filament.conversations.mark_read') }}</span>
            </x-filament::button>
        </div>
    </div>

    <!-- Messages Container -->
    <div
        class="gowa-chat-messages"
        wire:poll.4s
        x-data="{
            scrollToBottom() {
                this.$nextTick(() => {
                    if (this.$refs.chatContainer) {
                        this.$refs.chatContainer.scrollTop = this.$refs.chatContainer.scrollHeight;
                    }
                });
            },
            init() {
                this.scrollToBottom();
                const observer = new MutationObserver(() => this.scrollToBottom());
                observer.observe(this.$refs.chatContainer, { childList: true, subtree: true });
            }
        }"
        x-ref="chatContainer"
    >
        @php
            $lastDate = null;
        @endphp

        @forelse($this->messages as $message)
            @php
                $isInbound = $message->isInbound();
                $messageDate = $message->sent_at ? $message->sent_at->format('Y-m-d') : ($message->created_at ? $message->created_at->format('Y-m-d') : null);
            @endphp

            @if($messageDate && $lastDate !== $messageDate)
                @php
                    $lastDate = $messageDate;
                    $carbonDate = $message->sent_at ?? $message->created_at;
                @endphp
                <div class="gowa-date-separator">
                    <span class="gowa-date-badge">
                        {{ \Gowa\Filament\Pages\GowaConversationsPage::dateLabel($carbonDate) }}
                    </span>
                </div>
            @endif

            <div class="gowa-msg-row {{ $isInbound ? 'is-inbound' : 'is-outbound' }}">
                <div class="gowa-bubble {{ $isInbound ? 'is-inbound' : 'is-outbound' }}">
                    <!-- Media / Attachment Display -->
                    @if($message->type === 'image')
                        @if($message->media_url)
                            <a href="{{ $message->media_url }}" target="_blank" style="display: block; margin-bottom: 0.25rem; overflow: hidden; border-radius: 0.35rem;">
                                <img
                                    src="{{ $message->media_url }}"
                                    alt="WhatsApp Image"
                                    style="max-height: 13rem; max-width: 100%; width: auto; object-fit: cover; border-radius: 0.35rem; display: block;"
                                    loading="lazy"
                                />
                            </a>
                        @else
                            <div style="font-size: 0.75rem; font-weight: 600; margin-bottom: 0.2rem;">
                                📷 {{ \Gowa\Filament\Pages\GowaConversationsPage::messageTypeLabel('image') }}
                            </div>
                        @endif
                    @elseif($message->type === 'video')
                        @if($message->media_url)
                            <video src="{{ $message->media_url }}" controls style="max-height: 13rem; max-width: 100%; border-radius: 0.35rem; margin-bottom: 0.25rem; display: block;"></video>
                        @else
                            <div style="font-size: 0.75rem; font-weight: 600; margin-bottom: 0.2rem;">
                                🎥 {{ \Gowa\Filament\Pages\GowaConversationsPage::messageTypeLabel('video') }}
                            </div>
                        @endif
                    @elseif($message->type === 'audio')
                        @if($message->media_url)
                            <div style="padding: 0.15rem 0; min-width: 180px; max-width: 230px;">
                                <audio src="{{ $message->media_url }}" controls style="width: 100%; height: 1.875rem;"></audio>
                            </div>
                        @else
                            <div style="font-size: 0.75rem; font-weight: 600; margin-bottom: 0.2rem;">
                                🎙️ {{ \Gowa\Filament\Pages\GowaConversationsPage::messageTypeLabel('audio') }}
                            </div>
                        @endif
                    @elseif($message->type === 'document')
                        <div style="display: flex; align-items: center; gap: 0.5rem; padding: 0.35rem 0.5rem; border-radius: 0.35rem; background-color: rgba(0, 0, 0, 0.05); margin: 0.15rem 0;">
                            <x-filament::icon icon="heroicon-o-document-text" class="h-6 w-6 text-sky-600 shrink-0" />
                            <div style="flex: 1; min-width: 0;">
                                <p style="font-size: 0.75rem; font-weight: 600; margin: 0; overflow: hidden; text-overflow: ellipsis; white-space: nowrap;">
                                    {{ $message->body ?: 'Document' }}
                                </p>
                            </div>
                            @if($message->media_url)
                                <a
                                    href="{{ $message->media_url }}"
                                    target="_blank"
                                    download
                                    title="{{ __('gowa-filament::gowa-filament.conversations.download') }}"
                                    style="padding: 0.2rem; border-radius: 0.25rem; color: var(--gowa-text-muted); display: inline-flex;"
                                >
                                    <x-filament::icon icon="heroicon-m-arrow-down-tray" class="h-4 w-4" />
                                </a>
                            @endif
                        </div>
                    @elseif($message->type === 'location')
                        <div style="display: flex; align-items: center; gap: 0.375rem; padding: 0.35rem 0.5rem; border-radius: 0.35rem; background-color: rgba(0, 0, 0, 0.05); margin: 0.15rem 0;">
                            <x-filament::icon icon="heroicon-m-map-pin" class="h-5 w-5 text-rose-500 shrink-0" />
                            <div style="flex: 1; min-width: 0;">
                                <span style="font-size: 0.75rem; font-weight: 500; display: block; overflow: hidden; text-overflow: ellipsis; white-space: nowrap;">{{ $message->body ?: 'Location' }}</span>
                                <a
                                    href="https://www.google.com/maps?q={{ urlencode($message->body ?? '') }}"
                                    target="_blank"
                                    style="font-size: 0.6875rem; color: #0284c7; text-decoration: underline;"
                                >
                                    {{ __('gowa-filament::gowa-filament.conversations.view_location') }}
                                </a>
                            </div>
                        </div>
                    @elseif($message->type === 'contact')
                        <div style="display: flex; align-items: center; gap: 0.375rem; padding: 0.35rem 0.5rem; border-radius: 0.35rem; background-color: rgba(0, 0, 0, 0.05); margin: 0.15rem 0;">
                            <div style="width: 1.75rem; height: 1.75rem; border-radius: 9999px; background-color: rgba(14, 165, 233, 0.1); color: #0284c7; display: flex; align-items: center; justify-content: center; font-size: 0.75rem; font-weight: 700;">
                                👤
                            </div>
                            <div style="flex: 1; min-width: 0;">
                                <p style="font-size: 0.75rem; font-weight: 600; margin: 0; overflow: hidden; text-overflow: ellipsis; white-space: nowrap;">{{ $message->body ?: 'Contact' }}</p>
                            </div>
                        </div>
                    @elseif($message->type !== 'text')
                        <div style="font-size: 0.75rem; font-weight: 600; margin-bottom: 0.2rem; opacity: 0.85;">
                            {{ \Gowa\Filament\Pages\GowaConversationsPage::messageTypeLabel($message->type) }}
                        </div>
                    @endif

                    <!-- Message Text Body -->
                    @if($message->body && $message->type !== 'document')
                        <span class="gowa-bubble-text">{{ $message->body }}</span>
                    @endif

                    <!-- Footer: Timestamp & Delivery Ticks -->
                    <span class="gowa-bubble-meta">
                        @if($message->sent_at)
                            <span>{{ $message->sent_at->format('H:i') }}</span>
                        @elseif($message->created_at)
                            <span>{{ $message->created_at->format('H:i') }}</span>
                        @endif

                        @if(! $isInbound)
                            <span style="display: inline-flex; align-items: center; margin-left: 0.125rem;">
                                @if($message->status === \Gowa\Laravel\Enums\GowaMessageStatus::Read)
                                    <span style="display: inline-flex; align-items: center; color: #53bdeb;" title="Read">
                                        <x-filament::icon icon="heroicon-m-check" style="width: 0.75rem; height: 0.75rem;" />
                                        <x-filament::icon icon="heroicon-m-check" style="width: 0.75rem; height: 0.75rem; margin-left: -0.4rem;" />
                                    </span>
                                @elseif($message->status === \Gowa\Laravel\Enums\GowaMessageStatus::Delivered)
                                    <span style="display: inline-flex; align-items: center; opacity: 0.7;" title="Delivered">
                                        <x-filament::icon icon="heroicon-m-check" style="width: 0.75rem; height: 0.75rem;" />
                                        <x-filament::icon icon="heroicon-m-check" style="width: 0.75rem; height: 0.75rem; margin-left: -0.4rem;" />
                                    </span>
                                @elseif($message->status === \Gowa\Laravel\Enums\GowaMessageStatus::Sent)
                                    <span style="display: inline-flex; align-items: center; opacity: 0.7;" title="Sent">
                                        <x-filament::icon icon="heroicon-m-check" style="width: 0.75rem; height: 0.75rem;" />
                                    </span>
                                @elseif($message->status === \Gowa\Laravel\Enums\GowaMessageStatus::Failed)
                                    <x-filament::icon icon="heroicon-m-exclamation-circle" style="width: 0.75rem; height: 0.75rem;" class="text-rose-500 inline" />
                                @else
                                    <x-filament::icon icon="heroicon-m-clock" style="width: 0.75rem; height: 0.75rem;" class="inline opacity-70" />
                                @endif
                            </span>
                        @endif
                    </span>
                </div>
            </div>
        @empty
            <div class="gowa-empty-state">
                <p style="font-size: 0.875rem; font-style: italic; color: var(--gowa-text-muted); margin: 0;">
                    {{ __('gowa-filament::gowa-filament.conversations.no_messages') }}
                </p>
            </div>
        @endforelse
    </div>

    <!-- Composer Area -->
    <div class="gowa-chat-composer">
        <div style="flex-shrink: 0;">
            {{ $this->sendAttachmentAction }}
        </div>

        <div style="flex: 1; min-width: 0;">
            <x-filament::input.wrapper>
                <x-filament::input
                    type="text"
                    wire:model="newMessage"
                    placeholder="{{ __('gowa-filament::gowa-filament.conversations.message_placeholder') }}"
                    autocomplete="off"
                    wire:keydown.enter.prevent="sendMessage"
                />
            </x-filament::input.wrapper>
        </div>

        <div style="flex-shrink: 0;">
            <x-filament::button
                wire:click="sendMessage"
                size="md"
                color="primary"
                icon="heroicon-m-paper-airplane"
                wire:loading.attr="disabled"
                wire:target="sendMessage"
            >
                <span class="hidden sm:inline">{{ __('gowa-filament::gowa-filament.conversations.send') }}</span>
            </x-filament::button>
        </div>
    </div>
@endif
