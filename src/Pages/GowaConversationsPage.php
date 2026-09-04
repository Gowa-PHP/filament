<?php

declare(strict_types=1);

namespace Gowa\Filament\Pages;

use DateTimeInterface;
use Exception;
use Filament\Actions\Action;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;
use Filament\Notifications\Notification;
use Filament\Pages\Page;
use Filament\Schemas\Schema;
use Filament\Support\Enums\Width;
use Gowa\Laravel\Enums\GowaMessageDirection;
use Gowa\Laravel\Enums\GowaMessageStatus;
use Gowa\Laravel\Facades\Gowa;
use Gowa\Laravel\Models\GowaConversation;
use Gowa\Laravel\Models\GowaInstance;
use Gowa\Laravel\Models\GowaMessage;
use Gowa\Sdk\Dto\MediaPayload;
use Gowa\Sdk\Dto\MediaType;
use Gowa\Sdk\Dto\MediaUpload;
use Gowa\Sdk\Dto\SentMessage;
use Illuminate\Contracts\Support\Arrayable;
use Illuminate\Contracts\Support\MessageBag as MessageBagContract;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\MessageBag;

class GowaConversationsPage extends Page implements HasForms
{
    use InteractsWithForms;

    protected string $view = 'gowa-filament::pages.gowa-conversations-page';

    public function getErrorBag(): MessageBagContract
    {
        $bag = \Livewire\store($this)->get('errorBag');

        if (! $bag instanceof MessageBagContract) {
            $bag = new MessageBag();
            \Livewire\store($this)->set('errorBag', $bag);
        }

        return $bag;
    }

    public function setErrorBag($bag): MessageBagContract
    {
        $bagInstance = match (true) {
            $bag instanceof MessageBagContract => $bag,
            $bag instanceof Arrayable          => new MessageBag($bag->toArray()),
            is_array($bag)                     => new MessageBag($bag),
            default                            => new MessageBag(),
        };

        \Livewire\store($this)->set('errorBag', $bagInstance);

        return $bagInstance;
    }

    /** @var string|null Selected instance device_id (null = all instances) */
    public ?string $selectedInstanceId = null;

    /** @var int|null Active conversation ID */
    public ?int $selectedConversationId = null;

    /** @var string Search query */
    public string $search = '';

    /** @var bool Filter to show only unread conversations */
    public bool $filterUnread = false;

    /** @var string New message text in the composer */
    public string $newMessage = '';

    public static function getNavigationGroup(): ?string
    {
        return config('gowa-filament.navigation.group', 'WhatsApp');
    }

    public static function getNavigationIcon(): string
    {
        return 'heroicon-o-inbox';
    }

    public static function getNavigationSort(): ?int
    {
        return 3;
    }

    public static function getNavigationLabel(): string
    {
        return __('gowa-filament::gowa-filament.navigation.conversations');
    }

    public function getTitle(): string
    {
        return __('gowa-filament::gowa-filament.conversations.title');
    }

    public function getSubheading(): ?string
    {
        return __('gowa-filament::gowa-filament.conversations.subheading');
    }

    public function mount(): void
    {
        $modelClass = config('gowa-filament.model', GowaInstance::class);
        $this->selectedInstanceId = $modelClass::query()
            ->where('status', 'open')
            ->value('device_id')
            ?? $modelClass::query()->value('device_id');

        $this->form->fill([
            'selectedInstanceId' => $this->selectedInstanceId,
        ]);
    }

    /**
     * Get the avatar URL for a given instance.
     */
    public static function instanceAvatarUrl($instance): string
    {
        if (! $instance) {
            return 'https://ui-avatars.com/api/?name=WA&color=128C7E&background=DCF8C6';
        }

        if (! empty($instance->avatar_url)) {
            return (string) $instance->avatar_url;
        }

        if (is_array($instance->meta ?? null) && ! empty($instance->meta['avatar_url'])) {
            return (string) $instance->meta['avatar_url'];
        }

        if (! empty($instance->device_id) && ! empty($instance->phone_number)) {
            $cached = cache()->remember('gowa_avatar_' . $instance->device_id, 86400, function () use ($instance) {
                try {
                    $avatar = Gowa::avatar($instance->device_id, $instance->phone_number);

                    return $avatar?->url;
                } catch (\Throwable) {
                    return null;
                }
            });

            if ($cached) {
                return $cached;
            }
        }

        $name = $instance->name ?: $instance->device_id ?: 'WA';

        return 'https://ui-avatars.com/api/?name=' . urlencode((string) $name) . '&color=128C7E&background=DCF8C6';
    }

    public function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                Select::make('selectedInstanceId')
                    ->hiddenLabel()
                    ->placeholder(__('gowa-filament::gowa-filament.conversations.all_instances'))
                    ->options(function () {
                        $modelClass = config('gowa-filament.model', GowaInstance::class);

                        return $modelClass::query()
                            ->orderBy('name')
                            ->get()
                            ->mapWithKeys(function ($instance) {
                                $avatar = static::instanceAvatarUrl($instance);
                                $name = e($instance->name ?: $instance->device_id);

                                return [
                                    $instance->device_id => '<div style="display: flex; align-items: center; gap: 0.5rem;"><img src="' . e($avatar) . '" style="width: 1.25rem; height: 1.25rem; border-radius: 9999px; object-fit: cover;" /> <span>' . $name . '</span></div>',
                                ];
                            })
                            ->all();
                    })
                    ->allowHtml()
                    ->native(false)
                    ->live()
                    ->afterStateUpdated(function ($state) {
                        $this->selectedInstanceId = $state;
                        $this->updatedSelectedInstanceId();
                    }),
            ]);
    }

    public function updatedSelectedInstanceId(): void
    {
        // When instance filter changes, clear the active conversation selection if it doesn't belong to the instance
        if ($this->selectedInstanceId && $this->selectedConversationId) {
            $active = $this->activeConversation;
            if ($active && $active->instance?->device_id !== $this->selectedInstanceId) {
                $this->selectedConversationId = null;
            }
        }
    }

    /**
     * Get all available WhatsApp instances.
     *
     * @return Collection<int, GowaInstance>
     */
    public function getInstancesProperty(): Collection
    {
        $modelClass = config('gowa-filament.model', GowaInstance::class);

        return $modelClass::query()->orderBy('name')->get();
    }

    /**
     * Get filtered conversations for the selected instance.
     *
     * @return Collection<int, GowaConversation>
     */
    public function getConversationsProperty(): Collection
    {
        $query = GowaConversation::query()
            ->with(['instance', 'messages' => function ($q) {
                $q->latest('sent_at')->latest('created_at')->limit(1);
            }])
            ->withCount(['messages as unread_count' => function ($q) {
                $q->where('direction', GowaMessageDirection::Inbound->value)
                    ->whereNull('read_at');
            }]);

        // Filter by instance
        if ($this->selectedInstanceId) {
            $instanceModel = config('gowa-filament.model', GowaInstance::class);
            $instance = $instanceModel::where('device_id', $this->selectedInstanceId)->first();

            if ($instance) {
                $query->where('instance_id', $instance->id);
            }
        }

        // Search filter
        if (! empty($this->search)) {
            $search = $this->search;
            $query->where(function ($q) use ($search) {
                $q->where('contact_name', 'like', "%{$search}%")
                    ->orWhere('contact_phone', 'like', "%{$search}%")
                    ->orWhere('contact_jid', 'like', "%{$search}%")
                    ->orWhereHas('messages', function ($mq) use ($search) {
                        $mq->where('body', 'like', "%{$search}%");
                    });
            });
        }

        // Unread filter
        if ($this->filterUnread) {
            $query->whereHas('messages', function ($q) {
                $q->where('direction', GowaMessageDirection::Inbound->value)
                    ->whereNull('read_at');
            });
        }

        return $query->orderByDesc('last_message_at')->get();
    }

    /**
     * Total unread count across conversations.
     */
    public function getUnreadTotalCountProperty(): int
    {
        $query = GowaMessage::query()
            ->where('direction', GowaMessageDirection::Inbound->value)
            ->whereNull('read_at');

        if ($this->selectedInstanceId) {
            $instanceModel = config('gowa-filament.model', GowaInstance::class);
            $instance = $instanceModel::where('device_id', $this->selectedInstanceId)->first();
            if ($instance) {
                $query->where('instance_id', $instance->id);
            }
        }

        return $query->count();
    }

    /**
     * Get the active conversation object.
     */
    public function getActiveConversationProperty(): ?GowaConversation
    {
        if (! $this->selectedConversationId) {
            return null;
        }

        return GowaConversation::with('instance')->find($this->selectedConversationId);
    }

    /**
     * Get messages for the selected conversation.
     *
     * @return Collection<int, GowaMessage>
     */
    public function getMessagesProperty(): Collection
    {
        if (! $this->selectedConversationId) {
            return new Collection();
        }

        return GowaMessage::where('conversation_id', $this->selectedConversationId)
            ->orderBy('sent_at')
            ->orderBy('created_at')
            ->get();
    }

    /**
     * Select a conversation and load its messages.
     */
    public function selectConversation(int $id): void
    {
        $this->selectedConversationId = $id;
        $this->newMessage = '';

        $this->markConversationRead($id, notify: false);
    }

    /**
     * Send a text message in the active conversation.
     */
    public function sendMessage(): void
    {
        $text = trim($this->newMessage);

        if (empty($text) || ! $this->activeConversation) {
            return;
        }

        try {
            $conversation = $this->activeConversation;
            $instance = $conversation->instance;

            if (! $instance) {
                throw new Exception(__('gowa-filament::gowa-filament.notifications.error_occurred'));
            }

            $sentMessage = Gowa::sendText(
                $instance->device_id,
                $conversation->contact_jid,
                $text,
            );

            // Record message locally for instantaneous feedback
            $providerId = $sentMessage instanceof SentMessage ? $sentMessage->providerMessageId : 'out_' . uniqid();
            $messageModel = config('gowa.models.message', GowaMessage::class);
            $messageModel::create([
                'instance_id'     => $instance->id,
                'conversation_id' => $conversation->id,
                'message_id'      => $providerId,
                'direction'       => GowaMessageDirection::Outbound,
                'status'          => GowaMessageStatus::Sent,
                'type'            => 'text',
                'body'            => $text,
                'sent_at'         => now(),
            ]);

            $conversation->update(['last_message_at' => now()]);

            $this->newMessage = '';

            Notification::make()
                ->title(__('gowa-filament::gowa-filament.notifications.message_sent'))
                ->success()
                ->send();
        } catch (Exception $e) {
            Log::error('GOWA Conversations Page Error: ' . $e->getMessage(), [
                'exception' => $e,
            ]);

            Notification::make()
                ->title(__('gowa-filament::gowa-filament.notifications.error_occurred'))
                ->body($e->getMessage())
                ->danger()
                ->send();
        }
    }

    /**
     * Filament Action for sending attachments (images, documents, videos, audio).
     */
    public function sendAttachmentAction(): Action
    {
        return Action::make('sendAttachment')
            ->label(__('gowa-filament::gowa-filament.conversations.attach_file'))
            ->icon('heroicon-m-paper-clip')
            ->color('gray')
            ->tooltip(__('gowa-filament::gowa-filament.conversations.attach_file'))
            ->modalHeading(__('gowa-filament::gowa-filament.conversations.media_modal_title'))
            ->modalWidth(Width::Medium)
            ->form([
                Select::make('media_type')
                    ->label(__('gowa-filament::gowa-filament.messaging.media_type'))
                    ->options([
                        'image'    => __('gowa-filament::gowa-filament.conversations.message_types.image'),
                        'document' => __('gowa-filament::gowa-filament.conversations.message_types.document'),
                        'video'    => __('gowa-filament::gowa-filament.conversations.message_types.video'),
                        'audio'    => __('gowa-filament::gowa-filament.conversations.message_types.audio'),
                    ])
                    ->default('image')
                    ->native(false)
                    ->required(),
                FileUpload::make('attachment')
                    ->label(__('gowa-filament::gowa-filament.conversations.file_label'))
                    ->required()
                    ->maxSize(51200)
                    ->disk(config('filament.default_filesystem_disk', 'public'))
                    ->directory('gowa-attachments'),
                TextInput::make('caption')
                    ->label(__('gowa-filament::gowa-filament.conversations.caption_label'))
                    ->placeholder('...'),
            ])
            ->action(function (array $data): void {
                $this->sendAttachment($data);
            });
    }

    /**
     * Send attachment logic.
     */
    public function sendAttachment(array $data): void
    {
        if (! $this->activeConversation) {
            return;
        }

        try {
            $conversation = $this->activeConversation;
            $instance = $conversation->instance;

            if (! $instance) {
                throw new Exception(__('gowa-filament::gowa-filament.notifications.error_occurred'));
            }

            $type = $data['media_type'] ?? 'image';
            $caption = ! empty($data['caption']) ? trim((string) $data['caption']) : null;
            $filePath = $data['attachment'] ?? null;

            if (! $filePath) {
                throw new Exception('No attachment file provided.');
            }

            $diskName = config('filament.default_filesystem_disk', 'public');
            $disk = Storage::disk($diskName);
            $fullPath = $disk->path($filePath);
            $mime = $disk->mimeType($filePath) ?: 'application/octet-stream';
            $mediaUrl = $disk->url($filePath);

            $mediaType = match ($type) {
                'video'    => MediaType::Video,
                'audio'    => MediaType::Audio,
                'document' => MediaType::Document,
                default    => MediaType::Image,
            };

            $upload = new MediaUpload(
                source: $fullPath,
                filename: basename($filePath),
                mimeType: $mime,
            );

            $payload = new MediaPayload(
                type: $mediaType,
                upload: $upload,
                caption: $caption,
                voice: $type === 'audio',
            );

            $sent = Gowa::sendMedia(
                $instance->device_id,
                $conversation->contact_jid,
                $payload,
            );

            $providerId = $sent instanceof SentMessage ? $sent->providerMessageId : 'media_' . uniqid();

            // Record message locally
            $messageModel = config('gowa.models.message', GowaMessage::class);
            $messageModel::create([
                'instance_id'     => $instance->id,
                'conversation_id' => $conversation->id,
                'message_id'      => $providerId,
                'direction'       => GowaMessageDirection::Outbound,
                'status'          => GowaMessageStatus::Sent,
                'type'            => $type,
                'body'            => $caption ?? basename($filePath),
                'media_url'       => $mediaUrl,
                'media_mime'      => $mime,
                'sent_at'         => now(),
            ]);

            $conversation->update(['last_message_at' => now()]);

            Notification::make()
                ->title(__('gowa-filament::gowa-filament.notifications.message_sent'))
                ->success()
                ->send();
        } catch (Exception $e) {
            Log::error('GOWA Attachment Send Error: ' . $e->getMessage(), [
                'exception' => $e,
            ]);

            Notification::make()
                ->title(__('gowa-filament::gowa-filament.notifications.error_occurred'))
                ->body($e->getMessage())
                ->danger()
                ->send();
        }
    }

    /**
     * Mark all inbound messages in the active conversation as read.
     */
    public function markConversationRead(?int $conversationId = null, bool $notify = true): void
    {
        $targetId = $conversationId ?? $this->selectedConversationId;

        if (! $targetId) {
            return;
        }

        try {
            $conversation = GowaConversation::with('instance')->find($targetId);

            if (! $conversation) {
                return;
            }

            $unreadMessages = GowaMessage::where('conversation_id', $targetId)
                ->where('direction', GowaMessageDirection::Inbound->value)
                ->whereNull('read_at')
                ->get();

            // 1. Always mark as read locally in database first
            if ($unreadMessages->isNotEmpty()) {
                GowaMessage::where('conversation_id', $targetId)
                    ->where('direction', GowaMessageDirection::Inbound->value)
                    ->whereNull('read_at')
                    ->update([
                        'read_at' => now(),
                        'status'  => GowaMessageStatus::Read->value,
                    ]);
            }

            // 2. Call GOWA API for external WhatsApp read receipts
            if ($conversation->instance) {
                foreach ($unreadMessages as $message) {
                    try {
                        Gowa::markRead(
                            $conversation->instance->device_id,
                            $conversation->contact_jid,
                            $message->message_id,
                        );
                    } catch (\Throwable $e) {
                        Log::warning("Could not send read receipt to GOWA for message {$message->message_id}: " . $e->getMessage());
                    }
                }
            }

            if ($notify) {
                Notification::make()
                    ->title(__('gowa-filament::gowa-filament.conversations.marked_read'))
                    ->success()
                    ->send();
            }
        } catch (Exception $e) {
            Log::error('GOWA Mark Read Error: ' . $e->getMessage(), [
                'exception' => $e,
            ]);
        }
    }

    /**
     * Get the display name for a message type.
     */
    public static function messageTypeLabel(string $type): string
    {
        $icon = match ($type) {
            'image'    => '📷',
            'video'    => '🎥',
            'audio'    => '🎙️',
            'document' => '📄',
            'location' => '📍',
            'contact'  => '👤',
            'sticker'  => '🏷️',
            'poll'     => '📊',
            default    => '',
        };

        $label = __("gowa-filament::gowa-filament.conversations.message_types.{$type}");

        if ($label === "gowa-filament::gowa-filament.conversations.message_types.{$type}") {
            $label = ucfirst($type);
        }

        return trim("{$icon} {$label}");
    }

    /**
     * Helper to format a friendly date separator badge.
     */
    public static function dateLabel(?DateTimeInterface $date): string
    {
        if (! $date) {
            return '';
        }

        $carbon = Carbon::instance($date);

        if ($carbon->isToday()) {
            return __('gowa-filament::gowa-filament.conversations.today');
        }

        if ($carbon->isYesterday()) {
            return __('gowa-filament::gowa-filament.conversations.yesterday');
        }

        return $carbon->translatedFormat('d/m/Y');
    }

    /**
     * Helper to get initials from contact name or phone.
     */
    public static function contactInitials(?string $name, ?string $phone): string
    {
        if (! empty($name)) {
            $words = preg_split('/\s+/', trim($name));
            if (count($words) >= 2 && ! empty($words[0]) && ! empty($words[1])) {
                return mb_strtoupper(mb_substr($words[0], 0, 1) . mb_substr($words[1], 0, 1));
            }

            return mb_strtoupper(mb_substr($name, 0, 2));
        }

        if (! empty($phone)) {
            $digits = preg_replace('/\D/', '', $phone);

            return '#' . substr($digits, -2);
        }

        return 'WA';
    }

    /**
     * Helper to get deterministic avatar background styling.
     */
    public static function avatarBgColor(string $identifier): string
    {
        $palettes = [
            'bg-sky-100 text-sky-700 dark:bg-sky-500/20 dark:text-sky-300',
            'bg-emerald-100 text-emerald-700 dark:bg-emerald-500/20 dark:text-emerald-300',
            'bg-amber-100 text-amber-700 dark:bg-amber-500/20 dark:text-amber-300',
            'bg-purple-100 text-purple-700 dark:bg-purple-500/20 dark:text-purple-300',
            'bg-rose-100 text-rose-700 dark:bg-rose-500/20 dark:text-rose-300',
            'bg-indigo-100 text-indigo-700 dark:bg-indigo-500/20 dark:text-indigo-300',
            'bg-teal-100 text-teal-700 dark:bg-teal-500/20 dark:text-teal-300',
            'bg-violet-100 text-violet-700 dark:bg-violet-500/20 dark:text-violet-300',
        ];

        $hash = crc32($identifier);
        $index = abs($hash) % count($palettes);

        return $palettes[$index];
    }
}
