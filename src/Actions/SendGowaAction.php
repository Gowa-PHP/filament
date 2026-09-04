<?php

declare(strict_types=1);

namespace Gowa\Filament\Actions;

use Closure;
use Exception;
use Filament\Actions\Action;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Notifications\Notification;
use Filament\Support\Enums\Width;
use Gowa\Laravel\Facades\Gowa;
use Gowa\Laravel\Models\GowaInstance;
use Gowa\Laravel\PendingMessage;
use Gowa\Sdk\Dto\SentMessage;
use Illuminate\Database\Eloquent\Model;

class SendGowaAction extends Action
{
    protected string|Closure|null $toResolver = null;
    protected string|Closure|null $fromResolver = null;
    protected string|Closure|null $diskResolver = null;
    protected string|Closure|null $replyToResolver = null;

    protected string|Closure|null $textResolver = null;

    /** @var array{file: mixed, caption: string|Closure|null}|null */
    protected ?array $imageConfig = null;

    /** @var array{file: mixed, caption: string|Closure|null}|null */
    protected ?array $videoConfig = null;

    /** @var mixed */
    protected mixed $audioFile = null;

    /** @var mixed */
    protected mixed $voiceFile = null;

    /** @var array{file: mixed, filename: string|Closure|null, caption: string|Closure|null}|null */
    protected ?array $documentConfig = null;

    /** @var array{lat: float|Closure, lng: float|Closure|null}|null */
    protected ?array $locationConfig = null;

    /** @var array{name: string|Closure, phone: string|Closure}|null */
    protected ?array $contactConfig = null;

    /** @var array{question: string|Closure, options: array|Closure, maxSelections: int}|null */
    protected ?array $pollConfig = null;

    /** @var array{url: string|Closure, caption: string|Closure|null}|null */
    protected ?array $linkConfig = null;

    /** @var Closure|null fn(PendingMessage $message, ?Model $record): (PendingMessage|SentMessage) */
    protected ?Closure $fluentCallback = null;

    protected bool $isDirect = false;
    protected bool $isInstanceFromRecord = false;

    public static function getDefaultName(): ?string
    {
        return 'sendGowa';
    }

    protected function setUp(): void
    {
        parent::setUp();

        $this->label(__('gowa-filament::gowa-filament.actions.send_message'))
            ->icon('heroicon-o-paper-airplane')
            ->color('success')
            ->modalHeading(__('gowa-filament::gowa-filament.actions.send_message'))
            ->modalWidth(Width::Medium)
            ->form(function (self $action, ?Model $record): array {
                if ($action->isDirect) {
                    return [];
                }

                return $action->getFormSchema($record);
            })
            ->action(function (array $data, self $action, ?Model $record): void {
                $action->executeSend($data, $record);
            });
    }

    /**
     * Set the recipient phone number or column name/closure.
     */
    public function to(string|Closure $to): static
    {
        $this->toResolver = $to;

        return $this;
    }

    /**
     * Alias for to()
     */
    public function number(string|Closure $number): static
    {
        return $this->to($number);
    }

    /**
     * Set origin device ID / instance.
     */
    public function from(string|Closure $deviceId): static
    {
        $this->fromResolver = $deviceId;

        return $this;
    }

    /**
     * Alias for from()
     */
    public function instance(string|Closure $instanceId): static
    {
        return $this->from($instanceId);
    }

    public function instanceFromRecord(bool $condition = true): static
    {
        $this->isInstanceFromRecord = $condition;

        return $this;
    }

    public function disk(string|Closure $disk): static
    {
        $this->diskResolver = $disk;

        return $this;
    }

    public function replyTo(string|Closure $messageId): static
    {
        $this->replyToResolver = $messageId;

        return $this;
    }

    public function text(string|Closure $text): static
    {
        $this->textResolver = $text;

        return $this;
    }

    /**
     * Alias for text()
     */
    public function message(string|Closure $message): static
    {
        return $this->text($message);
    }

    public function image(mixed $file, string|Closure|null $caption = null): static
    {
        $this->imageConfig = [
            'file'    => $file,
            'caption' => $caption,
        ];

        return $this;
    }

    public function video(mixed $file, string|Closure|null $caption = null): static
    {
        $this->videoConfig = [
            'file'    => $file,
            'caption' => $caption,
        ];

        return $this;
    }

    public function audio(mixed $file): static
    {
        $this->audioFile = $file;

        return $this;
    }

    public function voice(mixed $file): static
    {
        $this->voiceFile = $file;

        return $this;
    }

    public function document(mixed $file, string|Closure|null $filename = null, string|Closure|null $caption = null): static
    {
        $this->documentConfig = [
            'file'     => $file,
            'filename' => $filename,
            'caption'  => $caption,
        ];

        return $this;
    }

    public function location(float|Closure $lat, float|Closure|null $lng = null): static
    {
        $this->locationConfig = [
            'lat' => $lat,
            'lng' => $lng,
        ];

        return $this;
    }

    public function contact(string|Closure $name, string|Closure $phone): static
    {
        $this->contactConfig = [
            'name'  => $name,
            'phone' => $phone,
        ];

        return $this;
    }

    public function poll(string|Closure $question, array|Closure $options, int $maxSelections = 1): static
    {
        $this->pollConfig = [
            'question'      => $question,
            'options'       => $options,
            'maxSelections' => $maxSelections,
        ];

        return $this;
    }

    public function whatsappLink(string|Closure $url, string|Closure|null $caption = null): static
    {
        $this->linkConfig = [
            'url'     => $url,
            'caption' => $caption,
        ];

        return $this;
    }

    /**
     * Provide a custom fluent callback with full access to PendingMessage.
     *
     * Example:
     * ->fluent(fn(PendingMessage $msg, $record) => $msg->to($record->phone)->text('Hi!'))
     */
    public function fluent(Closure $callback): static
    {
        $this->fluentCallback = $callback;

        return $this;
    }

    /**
     * Execute directly on click without opening a modal form.
     */
    public function direct(bool $condition = true): static
    {
        $this->isDirect = $condition;

        return $this;
    }

    /**
     * Alias to disable modal (direct execution).
     */
    public function withoutModal(bool $condition = true): static
    {
        return $this->direct($condition);
    }

    public function resolveTo(?Model $record): ?string
    {
        if ($this->toResolver === null) {
            return $record?->phone_number ?? $record?->phone ?? null;
        }

        if (is_callable($this->toResolver)) {
            return (string) call_user_func($this->toResolver, $record);
        }

        if (is_string($this->toResolver) && $record !== null) {
            $fromData = data_get($record, $this->toResolver);
            if (! empty($fromData)) {
                return (string) $fromData;
            }
        }

        return (string) $this->toResolver;
    }

    public function resolveDeviceId(?Model $record): ?string
    {
        if ($this->isInstanceFromRecord && $record !== null) {
            return $record->device_id ?? (string) $record->getKey();
        }

        if ($this->fromResolver === null) {
            return null;
        }

        if (is_callable($this->fromResolver)) {
            return (string) call_user_func($this->fromResolver, $record);
        }

        return (string) $this->fromResolver;
    }

    public function getFormSchema(?Model $record): array
    {
        $resolvedTo = $this->resolveTo($record);
        $resolvedDeviceId = $this->resolveDeviceId($record);

        $modelClass = config('gowa-filament.model', GowaInstance::class);
        $instances = $modelClass::query()->pluck('name', 'device_id')->all();

        $schema = [];

        if (empty($resolvedDeviceId)) {
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
            ->default($resolvedTo);

        if ($this->documentConfig !== null) {
            $schema[] = TextInput::make('document_url')
                ->label(__('gowa-filament::gowa-filament.fields.media_url'))
                ->placeholder('https://exemplo.com/documento.pdf')
                ->prefixIcon('heroicon-o-link')
                ->default(is_string($this->documentConfig['file']) && filter_var($this->documentConfig['file'], FILTER_VALIDATE_URL) ? $this->documentConfig['file'] : null);

            $schema[] = TextInput::make('filename')
                ->label(__('gowa-filament::gowa-filament.fields.filename'))
                ->placeholder('documento.pdf')
                ->default(is_callable($this->documentConfig['filename']) ? call_user_func($this->documentConfig['filename'], $record) : $this->documentConfig['filename']);
        } elseif ($this->imageConfig !== null || $this->videoConfig !== null) {
            $schema[] = TextInput::make('media_url')
                ->label(__('gowa-filament::gowa-filament.fields.media_url'))
                ->placeholder('https://exemplo.com/imagem.jpg')
                ->prefixIcon('heroicon-o-link');

            $schema[] = TextInput::make('caption')
                ->label(__('gowa-filament::gowa-filament.fields.caption'))
                ->placeholder('Legenda da imagem/vídeo...');
        } else {
            $resolvedText = is_callable($this->textResolver)
                ? (string) call_user_func($this->textResolver, $record)
                : (string) $this->textResolver;

            $schema[] = Textarea::make('message')
                ->label(__('gowa-filament::gowa-filament.fields.message'))
                ->placeholder('Digite sua mensagem de WhatsApp...')
                ->required()
                ->rows(3)
                ->default($resolvedText);
        }

        return $schema;
    }

    public function executeSend(array $data, ?Model $record): void
    {
        try {
            $to = (string) ($data['to'] ?? $this->resolveTo($record));
            $deviceId = (string) ($data['device_id'] ?? $this->resolveDeviceId($record));

            if (empty($deviceId)) {
                $modelClass = config('gowa-filament.model', GowaInstance::class);
                $deviceId = (string) $modelClass::query()->value('device_id');
            }

            if (empty($deviceId)) {
                throw new Exception('Nenhuma instância do WhatsApp encontrada para realizar o envio.');
            }

            if (empty($to)) {
                throw new Exception('Destinatário não informado.');
            }

            // Start fluent message chain
            $pending = Gowa::to($to)->from($deviceId);

            if ($this->diskResolver !== null) {
                $disk = is_callable($this->diskResolver) ? (string) call_user_func($this->diskResolver, $record) : (string) $this->diskResolver;
                $pending->disk($disk);
            }

            if ($this->replyToResolver !== null) {
                $replyTo = is_callable($this->replyToResolver) ? (string) call_user_func($this->replyToResolver, $record) : (string) $this->replyToResolver;
                $pending->replyTo($replyTo);
            }

            // If custom fluent closure provided, delegate to it
            if ($this->fluentCallback !== null) {
                $result = call_user_func($this->fluentCallback, $pending, $record);
                if ($result instanceof PendingMessage) {
                    $result->send();
                }
            } elseif ($this->documentConfig !== null) {
                $file = ! empty($data['document_url']) ? $data['document_url'] : $this->resolveValue($this->documentConfig['file'], $record);
                $filename = $data['filename'] ?? $this->resolveValue($this->documentConfig['filename'], $record);
                $caption = $this->resolveValue($this->documentConfig['caption'], $record);

                $pending->document($file, filename: $filename, caption: $caption)->send();
            } elseif ($this->imageConfig !== null) {
                $file = ! empty($data['media_url']) ? $data['media_url'] : $this->resolveValue($this->imageConfig['file'], $record);
                $caption = $data['caption'] ?? $this->resolveValue($this->imageConfig['caption'], $record);

                $pending->image($file, caption: $caption)->send();
            } elseif ($this->videoConfig !== null) {
                $file = ! empty($data['media_url']) ? $data['media_url'] : $this->resolveValue($this->videoConfig['file'], $record);
                $caption = $data['caption'] ?? $this->resolveValue($this->videoConfig['caption'], $record);

                $pending->video($file, caption: $caption)->send();
            } elseif ($this->audioFile !== null) {
                $file = $this->resolveValue($this->audioFile, $record);
                $pending->audio($file)->send();
            } elseif ($this->voiceFile !== null) {
                $file = $this->resolveValue($this->voiceFile, $record);
                $pending->voice($file)->send();
            } elseif ($this->locationConfig !== null) {
                $lat = (float) $this->resolveValue($this->locationConfig['lat'], $record);
                $lng = (float) $this->resolveValue($this->locationConfig['lng'], $record);
                $pending->location($lat, $lng)->send();
            } elseif ($this->contactConfig !== null) {
                $name = (string) $this->resolveValue($this->contactConfig['name'], $record);
                $phone = (string) $this->resolveValue($this->contactConfig['phone'], $record);
                $pending->contact($name, $phone)->send();
            } elseif ($this->pollConfig !== null) {
                $question = (string) $this->resolveValue($this->pollConfig['question'], $record);
                $options = (array) $this->resolveValue($this->pollConfig['options'], $record);
                $pending->poll($question, $options, $this->pollConfig['maxSelections'])->send();
            } elseif ($this->linkConfig !== null) {
                $url = (string) $this->resolveValue($this->linkConfig['url'], $record);
                $caption = $this->resolveValue($this->linkConfig['caption'], $record);
                $pending->link($url, $caption)->send();
            } else {
                $text = (string) ($data['message'] ?? $this->resolveValue($this->textResolver, $record));
                $pending->text($text)->send();
            }

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

    protected function resolveValue(mixed $value, ?Model $record): mixed
    {
        if (is_callable($value)) {
            return call_user_func($value, $record);
        }

        return $value;
    }
}
