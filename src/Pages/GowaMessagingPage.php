<?php

namespace Gowa\Filament\Pages;

use Exception;
use Filament\Actions\Action;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;
use Filament\Notifications\Notification;
use Filament\Pages\Page;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Filament\Support\Enums\Width;
use Gowa\Laravel\Facades\Gowa;
use Gowa\Laravel\Models\GowaInstance;
use Gowa\Sdk\Dto\ContactCard;
use Gowa\Sdk\Dto\MediaPayload;
use Gowa\Sdk\Dto\MediaType;
use Gowa\Sdk\Dto\MediaUpload;
use Gowa\Sdk\Dto\Presence;
use Illuminate\Contracts\Support\Arrayable;
use Illuminate\Contracts\Support\MessageBag as MessageBagContract;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\MessageBag;

class GowaMessagingPage extends Page implements HasForms
{
    use InteractsWithForms;

    protected string $view = 'gowa-filament::pages.gowa-messaging-page';

    public ?array $data = [];

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
            $bag instanceof Arrayable => new MessageBag($bag->toArray()),
            is_array($bag) => new MessageBag($bag),
            default => new MessageBag(),
        };

        \Livewire\store($this)->set('errorBag', $bagInstance);

        return $bagInstance;
    }

    public static function getNavigationGroup(): ?string
    {
        return config('gowa-filament.navigation.group', 'WhatsApp');
    }

    public static function getNavigationIcon(): string
    {
        return 'heroicon-o-paper-airplane';
    }

    public static function getNavigationSort(): ?int
    {
        return 2;
    }

    public static function getNavigationLabel(): string
    {
        return __('gowa-filament::gowa-filament.navigation.messaging');
    }

    public function getTitle(): string
    {
        return __('gowa-filament::gowa-filament.messaging.title');
    }

    public function getSubheading(): ?string
    {
        return __('gowa-filament::gowa-filament.messaging.subheading');
    }

    public function mount(): void
    {
        $modelClass = config('gowa-filament.model', GowaInstance::class);
        $defaultInstance = $modelClass::query()->where('status', 'open')->value('device_id')
            ?? $modelClass::query()->value('device_id');

        $this->form->fill([
            'device_id' => $defaultInstance,
            'recipient_type' => 'private',
            'to' => '',
            'message_type' => 'text',
            'is_voice' => true,
            'selectable_count' => 1,
            'presence_type' => 'composing',
        ]);
    }

    public function form(Schema $schema): Schema
    {
        $modelClass = config('gowa-filament.model', GowaInstance::class);
        $instances = $modelClass::query()->pluck('name', 'device_id')->all();

        return $schema
            ->components([
                Section::make(__('gowa-filament::gowa-filament.messaging.recipient_section'))
                    ->schema([
                        Grid::make(3)->schema([
                            Select::make('device_id')
                                ->label(__('gowa-filament::gowa-filament.fields.instance'))
                                ->options($instances)
                                ->required(),

                            Select::make('recipient_type')
                                ->label(__('gowa-filament::gowa-filament.fields.recipient_type'))
                                ->options([
                                    'private' => 'Mensagem Privada (Número)',
                                    'group' => 'Grupo (Group JID)',
                                ])
                                ->default('private')
                                ->required(),

                            TextInput::make('to')
                                ->label(__('gowa-filament::gowa-filament.fields.recipient_number'))
                                ->placeholder(fn ($get) => $get('recipient_type') === 'group' ? '12036304199999@g.us' : 'Ex: 5511999999999')
                                ->prefixIcon('heroicon-o-phone')
                                ->required(),
                        ]),
                    ]),

                Section::make(__('gowa-filament::gowa-filament.messaging.composer_section'))
                    ->schema([
                        Select::make('message_type')
                            ->label(__('gowa-filament::gowa-filament.fields.message_type'))
                            ->options([
                                'text' => '💬 Texto (Text)',
                                'image' => '🖼️ Imagem (Image)',
                                'video' => '🎥 Vídeo (Video)',
                                'document' => '📄 Documento (File)',
                                'audio' => '🎙️ Áudio / Voz (Audio PTT)',
                                'sticker' => '🏷️ Sticker',
                                'contact' => '👤 Contato (Contact)',
                                'location' => '📍 Localização (Location)',
                                'link' => '🔗 Link com Preview (Link)',
                                'poll' => '📊 Enquete (Poll)',
                                'presence' => '📡 Status de Presença (Presence)',
                            ])
                            ->live()
                            ->required(),

                        // Text Fields
                        Textarea::make('message')
                            ->label(__('gowa-filament::gowa-filament.fields.message'))
                            ->placeholder('Digite sua mensagem de WhatsApp...')
                            ->rows(4)
                            ->visible(fn ($get) => $get('message_type') === 'text')
                            ->required(fn ($get) => $get('message_type') === 'text'),

                        TextInput::make('reply_to')
                            ->label(__('gowa-filament::gowa-filament.fields.reply_to'))
                            ->placeholder('Ex: 3EB0C1234567890')
                            ->visible(fn ($get) => $get('message_type') === 'text'),

                        // Filament Native FileUpload Component with Image Editor and Dynamic Mimes
                        FileUpload::make('media_file')
                            ->label(__('gowa-filament::gowa-filament.fields.media_file'))
                            ->directory('gowa-media')
                            ->visibility('public')
                            ->preserveFilenames()
                            ->image(fn ($get) => in_array($get('message_type'), ['image', 'sticker'], true))
                            ->imageEditor(fn ($get) => $get('message_type') === 'image')
                            ->acceptedFileTypes(fn ($get) => match ($get('message_type')) {
                                'image', 'sticker' => ['image/jpeg', 'image/png', 'image/webp', 'image/gif'],
                                'video' => ['video/mp4', 'video/3gpp', 'video/quicktime', 'video/avi', 'video/x-msvideo'],
                                'audio' => ['audio/mp3', 'audio/ogg', 'audio/wav', 'audio/aac', 'audio/m4a', 'audio/mp4'],
                                default => null,
                            })
                            ->visible(fn ($get) => in_array($get('message_type'), ['image', 'video', 'document', 'audio', 'sticker'], true)),

                        // External URL fallback
                        TextInput::make('media_url')
                            ->label(__('gowa-filament::gowa-filament.fields.media_url'))
                            ->placeholder('https://exemplo.com/midia.jpg ou /caminho/local/midia.jpg')
                            ->prefixIcon('heroicon-o-link')
                            ->visible(fn ($get) => in_array($get('message_type'), ['image', 'video', 'document', 'audio', 'sticker'], true)),

                        TextInput::make('caption')
                            ->label(__('gowa-filament::gowa-filament.fields.caption'))
                            ->placeholder('Legenda da mídia (opcional)')
                            ->visible(fn ($get) => in_array($get('message_type'), ['image', 'video'], true)),

                        TextInput::make('filename')
                            ->label(__('gowa-filament::gowa-filament.fields.filename'))
                            ->placeholder('documento.pdf')
                            ->visible(fn ($get) => $get('message_type') === 'document'),

                        Toggle::make('is_voice')
                            ->label(__('gowa-filament::gowa-filament.fields.is_voice'))
                            ->default(true)
                            ->visible(fn ($get) => $get('message_type') === 'audio'),

                        // Contact Fields
                        Grid::make(2)->schema([
                            TextInput::make('contact_name')
                                ->label(__('gowa-filament::gowa-filament.fields.contact_name'))
                                ->placeholder('Ex: João Silva')
                                ->required(fn ($get) => $get('message_type') === 'contact'),

                            TextInput::make('contact_phone')
                                ->label(__('gowa-filament::gowa-filament.fields.contact_phone'))
                                ->placeholder('Ex: 5511988888888')
                                ->required(fn ($get) => $get('message_type') === 'contact'),
                        ])->visible(fn ($get) => $get('message_type') === 'contact'),

                        // Location Fields
                        Grid::make(2)->schema([
                            TextInput::make('latitude')
                                ->label(__('gowa-filament::gowa-filament.fields.latitude'))
                                ->placeholder('-23.550520')
                                ->numeric()
                                ->required(fn ($get) => $get('message_type') === 'location'),

                            TextInput::make('longitude')
                                ->label(__('gowa-filament::gowa-filament.fields.longitude'))
                                ->placeholder('-46.633308')
                                ->numeric()
                                ->required(fn ($get) => $get('message_type') === 'location'),

                            TextInput::make('location_name')
                                ->label(__('gowa-filament::gowa-filament.fields.location_name'))
                                ->placeholder('Ex: Escritório Atlântida Code'),

                            TextInput::make('address')
                                ->label(__('gowa-filament::gowa-filament.fields.address'))
                                ->placeholder('Ex: Av. Paulista, 1000 - São Paulo, SP'),
                        ])->visible(fn ($get) => $get('message_type') === 'location'),

                        // Link Fields
                        Grid::make(2)->schema([
                            TextInput::make('url')
                                ->label(__('gowa-filament::gowa-filament.fields.url'))
                                ->placeholder('https://atlantida-code.com.br')
                                ->url()
                                ->required(fn ($get) => $get('message_type') === 'link'),

                            TextInput::make('link_text')
                                ->label(__('gowa-filament::gowa-filament.fields.link_text'))
                                ->placeholder('Confira nosso novo site!'),
                        ])->visible(fn ($get) => $get('message_type') === 'link'),

                        // Poll Fields
                        Grid::make(1)->schema([
                            TextInput::make('question')
                                ->label(__('gowa-filament::gowa-filament.fields.question'))
                                ->placeholder('Qual o melhor horário para atendimento?')
                                ->required(fn ($get) => $get('message_type') === 'poll'),

                            Repeater::make('poll_options')
                                ->label(__('gowa-filament::gowa-filament.fields.poll_options'))
                                ->schema([
                                    TextInput::make('option_name')
                                        ->label(__('gowa-filament::gowa-filament.fields.poll_option_name'))
                                        ->required(),
                                ])
                                ->minItems(2)
                                ->default([
                                    ['option_name' => 'Manhã (08h - 12h)'],
                                    ['option_name' => 'Tarde (13h - 18h)'],
                                ]),

                            Select::make('selectable_count')
                                ->label(__('gowa-filament::gowa-filament.fields.selectable_count'))
                                ->options([
                                    1 => '1 opção',
                                    2 => '2 opções',
                                    3 => '3 opções',
                                ])
                                ->default(1),
                        ])->visible(fn ($get) => $get('message_type') === 'poll'),

                        // Presence Fields
                        Select::make('presence_type')
                            ->label(__('gowa-filament::gowa-filament.fields.presence_type'))
                            ->options([
                                'composing' => '⌨️ Digitando... (Composing)',
                                'recording' => '🎙️ Gravando áudio... (Recording)',
                                'paused' => '⏸️ Pausado (Paused)',
                            ])
                            ->default('composing')
                            ->visible(fn ($get) => $get('message_type') === 'presence'),
                    ]),
            ])
            ->statePath('data');
    }

    protected function resolveMediaUpload(array $data): MediaUpload
    {
        $mediaPath = null;
        $originalName = null;

        if (! empty($data['filename'])) {
            $originalName = trim((string) $data['filename']);
        }

        if (! empty($data['media_file'])) {
            $file = $data['media_file'];

            if (is_array($file)) {
                foreach ($file as $k => $v) {
                    if ($v instanceof \Illuminate\Http\UploadedFile) {
                        $file = $v;
                        break;
                    }
                    if (is_string($v) && ! empty($v)) {
                        $file = $v;
                        break;
                    }
                    if (is_string($k) && ! empty($k) && str_contains($k, '.')) {
                        $file = $k;
                        break;
                    }
                }
            }

            if ($file instanceof \Illuminate\Http\UploadedFile) {
                $mediaPath = $file->getRealPath();
                $originalName ??= $file->getClientOriginalName();
            } elseif (is_string($file)) {
                $originalName ??= basename($file);

                $candidates = [
                    $file,
                    Storage::disk('public')->path($file),
                    Storage::disk('local')->path($file),
                    storage_path('app/public/' . $file),
                    storage_path('app/' . $file),
                    storage_path('app/livewire-tmp/' . $file),
                    public_path('storage/' . $file),
                ];

                foreach ($candidates as $candidate) {
                    if (! empty($candidate) && file_exists($candidate) && ! is_dir($candidate)) {
                        $mediaPath = $candidate;
                        break;
                    }
                }
            }
        } elseif (! empty($data['media_url'])) {
            $mediaPath = trim((string) $data['media_url']);
        }

        if (empty($mediaPath)) {
            throw new Exception('Faça o upload de um arquivo ou informe a URL da mídia.');
        }

        return filter_var($mediaPath, FILTER_VALIDATE_URL)
            ? MediaUpload::fromUrl($mediaPath, filename: $originalName)
            : MediaUpload::fromPath($mediaPath, filename: $originalName);
    }

    public function send(): void
    {
        $data = $this->form->getState();

        try {
            $deviceId = $data['device_id'] ?? null;
            $to = $data['to'] ?? null;
            $type = $data['message_type'] ?? 'text';

            if (empty($deviceId) || empty($to)) {
                throw new Exception('Selecione a instância e informe o destinatário.');
            }

            match ($type) {
                'text' => Gowa::sendText($deviceId, $to, (string) ($data['message'] ?? ''), $data['reply_to'] ?? null),

                'image' => Gowa::sendMedia(
                    $deviceId,
                    $to,
                    new MediaPayload(
                        type: MediaType::Image,
                        upload: $this->resolveMediaUpload($data),
                        caption: $data['caption'] ?? null,
                    )
                ),

                'video' => Gowa::sendMedia(
                    $deviceId,
                    $to,
                    new MediaPayload(
                        type: MediaType::Video,
                        upload: $this->resolveMediaUpload($data),
                        caption: $data['caption'] ?? null,
                    )
                ),

                'document' => Gowa::sendMedia(
                    $deviceId,
                    $to,
                    new MediaPayload(
                        type: MediaType::Document,
                        upload: $this->resolveMediaUpload($data),
                    )
                ),

                'audio' => Gowa::sendMedia(
                    $deviceId,
                    $to,
                    new MediaPayload(
                        type: MediaType::Audio,
                        upload: $this->resolveMediaUpload($data),
                        voice: (bool) ($data['is_voice'] ?? true),
                    )
                ),

                'sticker' => Gowa::sendMedia(
                    $deviceId,
                    $to,
                    new MediaPayload(
                        type: MediaType::Sticker,
                        upload: $this->resolveMediaUpload($data),
                    )
                ),

                'contact' => Gowa::sendContacts(
                    $deviceId,
                    $to,
                    [new ContactCard(name: (string) $data['contact_name'], phones: [['phone' => (string) $data['contact_phone']]])]
                ),

                'location' => Gowa::sendLocation(
                    $deviceId,
                    $to,
                    (float) $data['latitude'],
                    (float) $data['longitude'],
                    $data['location_name'] ?? null,
                    $data['address'] ?? null
                ),

                'link' => Gowa::sendLink($deviceId, $to, (string) $data['url'], $data['link_text'] ?? null),

                'poll' => Gowa::sendPoll(
                    $deviceId,
                    $to,
                    (string) $data['question'],
                    array_values(array_filter(array_column($data['poll_options'] ?? [], 'option_name'))),
                    (int) ($data['selectable_count'] ?? 1)
                ),

                'presence' => Gowa::setPresence(
                    $deviceId,
                    match ($data['presence_type'] ?? 'composing') {
                        'recording' => Presence::Recording,
                        'paused' => Presence::Paused,
                        default => Presence::Composing,
                    }
                ),

                default => throw new Exception("Tipo de mensagem '{$type}' não suportado."),
            };

            // Reset media and message fields after successful dispatch
            $this->form->fill([
                'device_id' => $deviceId,
                'recipient_type' => $data['recipient_type'] ?? 'private',
                'to' => $to,
                'message_type' => $type,
                'message' => '',
                'reply_to' => '',
                'media_file' => null,
                'media_url' => '',
                'caption' => '',
                'filename' => '',
                'is_voice' => true,
                'contact_name' => '',
                'contact_phone' => '',
                'latitude' => '',
                'longitude' => '',
                'location_name' => '',
                'address' => '',
                'url' => '',
                'link_text' => '',
                'question' => '',
                'poll_options' => [
                    ['option_name' => 'Manhã (08h - 12h)'],
                    ['option_name' => 'Tarde (13h - 18h)'],
                ],
                'selectable_count' => 1,
                'presence_type' => 'composing',
            ]);

            Notification::make()
                ->title($type === 'presence' ? __('gowa-filament::gowa-filament.notifications.presence_updated') : __('gowa-filament::gowa-filament.notifications.message_sent'))
                ->success()
                ->send();
        } catch (Exception $e) {
            Log::error('GOWA Messaging Page Error: ' . $e->getMessage(), [
                'exception' => $e,
            ]);

            Notification::make()
                ->title(__('gowa-filament::gowa-filament.notifications.error_occurred'))
                ->body($e->getMessage())
                ->danger()
                ->send();
        }
    }

    public function viewCurl(): Action
    {
        return Action::make('viewCurl')
            ->label(__('gowa-filament::gowa-filament.actions.view_curl'))
            ->icon('heroicon-o-code-bracket')
            ->color('gray')
            ->modalHeading(__('gowa-filament::gowa-filament.messaging.curl_heading'))
            ->modalDescription(__('gowa-filament::gowa-filament.messaging.curl_desc'))
            ->modalWidth(Width::Medium)
            ->action(fn () => null);
    }
}
