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
use Gowa\Laravel\Models\GowaInstance;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Notifications\Notifiable;
use Illuminate\Notifications\Notification as LaravelNotification;
use Illuminate\Support\Facades\Notification as NotificationFacade;

class SendGowaNotificationAction extends Action
{
    protected string|Closure|null $numberResolver = null;
    protected string|Closure|null $instanceResolver = null;
    protected string|Closure|null $messageText = null;
    protected bool $isInstanceFromRecord = false;

    public static function getDefaultName(): ?string
    {
        return 'sendGowaNotification';
    }

    protected function setUp(): void
    {
        parent::setUp();

        $this->label(__('gowa-filament::gowa-filament.actions.send_notification', ['default' => 'Testar Laravel Notification']))
            ->icon('heroicon-o-bell')
            ->color('warning')
            ->modalHeading(__('gowa-filament::gowa-filament.actions.send_notification', ['default' => 'Testar Laravel Notification Channel']))
            ->modalWidth(Width::Medium)
            ->form(fn(self $action, ?Model $record): array => $action->getFormSchema($record))
            ->action(function (array $data, self $action, ?Model $record): void {
                $action->executeSendNotification($data, $record);
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
            ->placeholder('Digite a mensagem da notificação...')
            ->required()
            ->rows(3)
            ->default($this->resolveMessage($record) ?? '🔔 Teste de Notificação Nativa do Laravel via GOWA Channel!');

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

    public function executeSendNotification(array $data, ?Model $record): void
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

            $recipient = (string) ($data['to'] ?? '');
            $text = (string) ($data['message'] ?? '');

            $notifiable = new class ($deviceId, $recipient) {
                use Notifiable;

                public function __construct(private string $device, private string $phone) {}

                public function routeNotificationForGowa(): array
                {
                    return ['device' => $this->device, 'to' => $this->phone];
                }
            };

            $laravelNotification = new class ($text) extends LaravelNotification {
                public function __construct(private string $text) {}

                public function via(mixed $notifiable): array
                {
                    return [\Gowa\Laravel\Notifications\GowaChannel::class];
                }

                public function toGowa(mixed $notifiable): \Gowa\Laravel\Notifications\GowaMessage
                {
                    return \Gowa\Laravel\Notifications\GowaMessage::create($this->text);
                }
            };

            NotificationFacade::send($notifiable, $laravelNotification);

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
