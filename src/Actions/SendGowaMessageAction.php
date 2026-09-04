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
use Illuminate\Database\Eloquent\Model;

class SendGowaMessageAction extends Action
{
    protected string|Closure|null $numberResolver = null;
    protected string|Closure|null $instanceResolver = null;
    protected string|Closure|null $messageText = null;
    protected bool $isInstanceFromRecord = false;

    public static function getDefaultName(): ?string
    {
        return 'sendGowaMessage';
    }

    protected function setUp(): void
    {
        parent::setUp();

        $this->label(__('gowa-filament::gowa-filament.actions.send_message'))
            ->icon('heroicon-o-paper-airplane')
            ->color('success')
            ->modalHeading(__('gowa-filament::gowa-filament.actions.send_message'))
            ->modalWidth(Width::Medium)
            ->form(fn(self $action, ?Model $record): array => $action->getFormSchema($record))
            ->action(function (array $data, self $action, ?Model $record): void {
                $action->executeSendMessage($data, $record);
            });
    }

    public function to(string|Closure $to): static
    {
        return $this->number($to);
    }

    public function from(string|int|Closure $from): static
    {
        return $this->instance($from);
    }

    public function text(string|Closure $text): static
    {
        return $this->message($text);
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

    public function message(string|Closure $message): static
    {
        $this->messageText = $message;
        return $this;
    }

    public function getFormSchema(?Model $record): array
    {
        $resolvedNumber = $this->resolveNumber($record);
        $resolvedInstanceId = $this->resolveInstanceId($record);

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

        $schema[] = Textarea::make('message')
            ->label(__('gowa-filament::gowa-filament.fields.message'))
            ->placeholder('Digite sua mensagem de WhatsApp...')
            ->required()
            ->rows(3)
            ->default($this->resolveMessage($record));

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

    public function resolveMessage(?Model $record): ?string
    {
        if ($this->messageText === null) {
            return null;
        }

        if (is_callable($this->messageText)) {
            return (string) call_user_func($this->messageText, $record);
        }

        return (string) $this->messageText;
    }

    public function executeSendMessage(array $data, ?Model $record): void
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
            $text = (string) ($data['message'] ?? $this->resolveMessage($record));

            Gowa::to($recipient)
                ->from($deviceId)
                ->text($text)
                ->send();

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
