<?php

namespace Gowa\Filament\Actions;

use Closure;
use Exception;
use Filament\Actions\Action;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Notifications\Notification;
use Filament\Support\Enums\Width;
use Gowa\Laravel\Facades\Gowa;
use Gowa\Laravel\Models\GowaInstance;
use Gowa\Sdk\Dto\MediaPayload;
use Gowa\Sdk\Dto\MediaType;
use Gowa\Sdk\Dto\MediaUpload;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Storage;

class SendGowaMediaAction extends Action
{
    protected string|Closure|null $numberResolver = null;
    protected string|Closure|null $instanceResolver = null;
    protected string|Closure|null $mediaFileResolver = null;
    protected string|Closure|null $mediaUrlResolver = null;
    protected string|Closure|null $captionTextResolver = null;
    protected MediaType $mediaType = MediaType::Image;
    protected bool $isInstanceFromRecord = false;

    public static function getDefaultName(): ?string
    {
        return 'sendGowaMedia';
    }

    protected function setUp(): void
    {
        parent::setUp();

        $this->label(__('gowa-filament::gowa-filament.actions.send_media'))
            ->icon('heroicon-o-photo')
            ->color('success')
            ->modalHeading(__('gowa-filament::gowa-filament.actions.send_media'))
            ->modalWidth(Width::Medium)
            ->form(fn (self $action, ?Model $record): array => $action->getFormSchema($record))
            ->action(function (array $data, self $action, ?Model $record): void {
                $action->executeSendMedia($data, $record);
            });
    }

    public function type(MediaType $type): static
    {
        $this->mediaType = $type;
        return $this;
    }

    public function numberFrom(string|Closure $columnOrClosure): static
    {
        $this->numberResolver = $columnOrClosure;
        return $this;
    }

    public function number(string|Closure $number): static
    {
        $this->numberResolver = $number;
        return $this;
    }

    public function instance(string|int|Closure $instanceId): static
    {
        $this->instanceResolver = $instanceId;
        return $this;
    }

    public function instanceFromRecord(bool $condition = true): static
    {
        $this->isInstanceFromRecord = $condition;
        return $this;
    }

    public function mediaFrom(string|Closure $columnOrClosure): static
    {
        $this->mediaFileResolver = $columnOrClosure;
        return $this;
    }

    public function mediaUrl(string|Closure $urlOrClosure): static
    {
        $this->mediaUrlResolver = $urlOrClosure;
        return $this;
    }

    public function caption(string|Closure $caption): static
    {
        $this->captionTextResolver = $caption;
        return $this;
    }

    public function getFormSchema(?Model $record): array
    {
        $resolvedNumber = $this->resolveNumber($record);
        $resolvedInstanceId = $this->resolveInstanceId($record);
        $resolvedUrl = $this->resolveMediaUrl($record);
        $resolvedCaption = $this->resolveCaption($record);

        $modelClass = config('gowa-filament.model', GowaInstance::class);
        $instances = $modelClass::query()->pluck('name', 'device_id')->all();

        $schema = [];

        if (empty($resolvedInstanceId)) {
            $schema[] = Select::make('device_id')
                ->label(__('gowa-filament::gowa-filament.fields.instance'))
                ->options($instances)
                ->required()
                ->default(array_key_first($instances));
        }

        $schema[] = TextInput::make('to')
            ->label(__('gowa-filament::gowa-filament.fields.recipient_number'))
            ->placeholder('Ex: 5511999999999')
            ->prefixIcon('heroicon-o-phone')
            ->required()
            ->default($resolvedNumber);

        if (empty($resolvedUrl)) {
            $schema[] = FileUpload::make('media_file')
                ->label(__('gowa-filament::gowa-filament.fields.media_file'))
                ->directory('gowa-media')
                ->visibility('public')
                ->maxSize(51200)
                ->preserveFilenames()
                ->imageEditor(fn () => $this->mediaType === MediaType::Image)
                ->acceptedFileTypes(match ($this->mediaType) {
                    MediaType::Image, MediaType::Sticker => ['image/jpeg', 'image/png', 'image/webp', 'image/gif'],
                    MediaType::Video => ['video/mp4', 'video/3gpp', 'video/quicktime', 'video/avi'],
                    MediaType::Audio => ['audio/mp3', 'audio/ogg', 'audio/wav', 'audio/aac', 'audio/m4a'],
                    default => null,
                });
        }

        $schema[] = TextInput::make('media_url')
            ->label(__('gowa-filament::gowa-filament.fields.media_url'))
            ->placeholder('https://exemplo.com/midia.jpg')
            ->prefixIcon('heroicon-o-link')
            ->default($resolvedUrl);

        if (in_array($this->mediaType, [MediaType::Image, MediaType::Video], true)) {
            $schema[] = TextInput::make('caption')
                ->label(__('gowa-filament::gowa-filament.fields.caption'))
                ->placeholder('Legenda da mídia (opcional)')
                ->default($resolvedCaption);
        }

        return $schema;
    }

    public function resolveNumber(?Model $record): ?string
    {
        if ($this->numberResolver === null) {
            return $record?->phone_number ?? $record?->phone ?? null;
        }

        if (is_callable($this->numberResolver)) {
            return (string) call_user_func($this->numberResolver, $record);
        }

        if (is_string($this->numberResolver) && $record !== null) {
            return (string) data_get($record, $this->numberResolver);
        }

        return (string) $this->numberResolver;
    }

    public function resolveInstanceId(?Model $record): ?string
    {
        if ($this->isInstanceFromRecord && $record !== null) {
            return $record->device_id ?? (string) $record->getKey();
        }

        if ($this->instanceResolver === null) {
            return null;
        }

        if (is_callable($this->instanceResolver)) {
            return (string) call_user_func($this->instanceResolver, $record);
        }

        return (string) $this->instanceResolver;
    }

    public function resolveMediaUrl(?Model $record): ?string
    {
        if ($this->mediaUrlResolver === null) {
            return null;
        }

        if (is_callable($this->mediaUrlResolver)) {
            return (string) call_user_func($this->mediaUrlResolver, $record);
        }

        if (is_string($this->mediaUrlResolver) && $record !== null) {
            return (string) data_get($record, $this->mediaUrlResolver);
        }

        return (string) $this->mediaUrlResolver;
    }

    public function resolveCaption(?Model $record): ?string
    {
        if ($this->captionTextResolver === null) {
            return null;
        }

        if (is_callable($this->captionTextResolver)) {
            return (string) call_user_func($this->captionTextResolver, $record);
        }

        if (is_string($this->captionTextResolver) && $record !== null) {
            return (string) data_get($record, $this->captionTextResolver);
        }

        return (string) $this->captionTextResolver;
    }

    protected function resolveMediaUpload(array $data, ?Model $record): MediaUpload
    {
        $mediaPath = null;
        $originalName = null;

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
                }
            }

            if ($file instanceof \Illuminate\Http\UploadedFile) {
                $mediaPath = $file->getRealPath();
                $originalName = $file->getClientOriginalName();
            } elseif (is_string($file)) {
                $originalName = basename($file);

                $candidates = [
                    $file,
                    Storage::disk('public')->path($file),
                    Storage::disk('local')->path($file),
                    storage_path('app/public/' . $file),
                    storage_path('app/' . $file),
                    storage_path('app/livewire-tmp/' . $file),
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
        } elseif ($this->mediaFileResolver !== null && $record !== null) {
            $resolvedFile = is_callable($this->mediaFileResolver)
                ? call_user_func($this->mediaFileResolver, $record)
                : data_get($record, $this->mediaFileResolver);

            if (! empty($resolvedFile) && is_string($resolvedFile)) {
                $originalName = basename($resolvedFile);

                $candidates = [
                    $resolvedFile,
                    Storage::disk('public')->path($resolvedFile),
                    Storage::disk('local')->path($resolvedFile),
                    storage_path('app/public/' . $resolvedFile),
                    storage_path('app/' . $resolvedFile),
                ];

                foreach ($candidates as $candidate) {
                    if (! empty($candidate) && file_exists($candidate) && ! is_dir($candidate)) {
                        $mediaPath = $candidate;
                        break;
                    }
                }
            }
        }

        if (empty($mediaPath)) {
            throw new Exception('Selecione um arquivo de mídia ou informe a URL da mídia.');
        }

        return filter_var($mediaPath, FILTER_VALIDATE_URL)
            ? MediaUpload::fromUrl($mediaPath, filename: $originalName)
            : MediaUpload::fromPath($mediaPath, filename: $originalName);
    }

    public function executeSendMedia(array $data, ?Model $record): void
    {
        try {
            $deviceId = $data['device_id'] ?? $this->resolveInstanceId($record);

            if (empty($deviceId)) {
                $modelClass = config('gowa-filament.model', GowaInstance::class);
                $deviceId = $modelClass::query()->value('device_id');
            }

            if (empty($deviceId)) {
                throw new Exception('Nenhuma instância do WhatsApp encontrada para realizar o envio.');
            }

            $recipient = (string) ($data['to'] ?? $this->resolveNumber($record));
            $caption = $data['caption'] ?? $this->resolveCaption($record);
            $upload = $this->resolveMediaUpload($data, $record);

            Gowa::sendMedia(
                $deviceId,
                $recipient,
                new MediaPayload(
                    type: $this->mediaType,
                    upload: $upload,
                    caption: $caption,
                )
            );

            Notification::make()
                ->title(__('gowa-filament::gowa-filament.notifications.message_sent'))
                ->success()
                ->send();
        } catch (Exception $e) {
            Notification::make()
                ->title(__('gowa-filament::gowa-filament.notifications.error_occurred'))
                ->body($e->getMessage())
                ->danger()
                ->send();
        }
    }
}
