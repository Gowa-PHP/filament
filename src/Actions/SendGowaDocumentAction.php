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

class SendGowaDocumentAction extends Action
{
    protected string|Closure|null $numberResolver = null;
    protected string|Closure|null $instanceResolver = null;
    protected string|Closure|null $documentFileResolver = null;
    protected string|Closure|null $documentUrlResolver = null;
    protected string|Closure|null $filenameResolver = null;
    protected bool $isInstanceFromRecord = false;

    public static function getDefaultName(): ?string
    {
        return 'sendGowaDocument';
    }

    protected function setUp(): void
    {
        parent::setUp();

        $this->label(__('gowa-filament::gowa-filament.actions.send_document'))
            ->icon('heroicon-o-document-text')
            ->color('info')
            ->modalHeading(__('gowa-filament::gowa-filament.actions.send_document'))
            ->modalWidth(Width::Medium)
            ->form(fn (self $action, ?Model $record): array => $action->getFormSchema($record))
            ->action(function (array $data, self $action, ?Model $record): void {
                $action->executeSendDocument($data, $record);
            });
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

    public function documentFrom(string|Closure $columnOrClosure): static
    {
        $this->documentFileResolver = $columnOrClosure;
        return $this;
    }

    public function documentUrl(string|Closure $urlOrClosure): static
    {
        $this->documentUrlResolver = $urlOrClosure;
        return $this;
    }

    public function filename(string|Closure $nameOrClosure): static
    {
        $this->filenameResolver = $nameOrClosure;
        return $this;
    }

    public function getFormSchema(?Model $record): array
    {
        $resolvedNumber = $this->resolveNumber($record);
        $resolvedInstanceId = $this->resolveInstanceId($record);
        $resolvedUrl = $this->resolveDocumentUrl($record);
        $resolvedFilename = $this->resolveFilename($record);

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
            $schema[] = FileUpload::make('document_file')
                ->label(__('gowa-filament::gowa-filament.fields.media_file'))
                ->directory('gowa-documents')
                ->visibility('public')
                ->maxSize(51200)
                ->preserveFilenames()
                ->acceptedFileTypes([
                    'application/pdf',
                    'text/csv',
                    'text/plain',
                    'application/msword',
                    'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
                    'application/vnd.ms-excel',
                    'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
                    'application/zip',
                    'application/x-zip-compressed',
                    'application/json',
                    'text/xml',
                ]);
        }

        $schema[] = TextInput::make('document_url')
            ->label(__('gowa-filament::gowa-filament.fields.media_url'))
            ->placeholder('https://exemplo.com/documento.pdf')
            ->prefixIcon('heroicon-o-link')
            ->default($resolvedUrl);

        $schema[] = TextInput::make('filename')
            ->label(__('gowa-filament::gowa-filament.fields.filename'))
            ->placeholder('documento.pdf')
            ->default($resolvedFilename);

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

    public function resolveDocumentUrl(?Model $record): ?string
    {
        if ($this->documentUrlResolver === null) {
            return null;
        }

        if (is_callable($this->documentUrlResolver)) {
            return (string) call_user_func($this->documentUrlResolver, $record);
        }

        if (is_string($this->documentUrlResolver) && $record !== null) {
            return (string) data_get($record, $this->documentUrlResolver);
        }

        return (string) $this->documentUrlResolver;
    }

    public function resolveFilename(?Model $record): ?string
    {
        if ($this->filenameResolver === null) {
            return null;
        }

        if (is_callable($this->filenameResolver)) {
            return (string) call_user_func($this->filenameResolver, $record);
        }

        if (is_string($this->filenameResolver) && $record !== null) {
            return (string) data_get($record, $this->filenameResolver);
        }

        return (string) $this->filenameResolver;
    }

    protected function resolveMediaUpload(array $data, ?Model $record): MediaUpload
    {
        $mediaPath = null;
        $originalName = $data['filename'] ?? $this->resolveFilename($record);

        if (! empty($data['document_file'])) {
            $file = $data['document_file'];

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
                ];

                foreach ($candidates as $candidate) {
                    if (! empty($candidate) && file_exists($candidate) && ! is_dir($candidate)) {
                        $mediaPath = $candidate;
                        break;
                    }
                }
            }
        } elseif (! empty($data['document_url'])) {
            $mediaPath = trim((string) $data['document_url']);
        } elseif ($this->documentFileResolver !== null && $record !== null) {
            $resolvedFile = is_callable($this->documentFileResolver)
                ? call_user_func($this->documentFileResolver, $record)
                : data_get($record, $this->documentFileResolver);

            if (! empty($resolvedFile) && is_string($resolvedFile)) {
                $originalName ??= basename($resolvedFile);

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
            throw new Exception('Selecione um arquivo de documento ou informe a URL do documento.');
        }

        return filter_var($mediaPath, FILTER_VALIDATE_URL)
            ? MediaUpload::fromUrl($mediaPath, filename: $originalName)
            : MediaUpload::fromPath($mediaPath, filename: $originalName);
    }

    public function executeSendDocument(array $data, ?Model $record): void
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
            $upload = $this->resolveMediaUpload($data, $record);

            Gowa::sendMedia(
                $deviceId,
                $recipient,
                new MediaPayload(
                    type: MediaType::Document,
                    upload: $upload,
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
