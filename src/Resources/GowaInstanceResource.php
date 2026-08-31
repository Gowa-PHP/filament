<?php

namespace Gowa\Filament\Resources;

use Filament\Forms\Components\TextInput;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Actions\ActionGroup;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ViewAction;
use Filament\Support\Enums\Operation;
use Filament\Support\Enums\Width;
use Filament\Tables\Columns\ImageColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Gowa\Filament\Actions\SendGowaMessageAction;
use Gowa\Filament\Resources\GowaInstanceResource\Actions\ConnectPairingCodeAction;
use Gowa\Filament\Resources\GowaInstanceResource\Actions\ConnectQrAction;
use Gowa\Filament\Resources\GowaInstanceResource\Actions\DisconnectAction;
use Gowa\Filament\Resources\GowaInstanceResource\Actions\RefreshStatusAction;
use Gowa\Filament\Resources\GowaInstanceResource\Pages\ListGowaInstances;
use Gowa\Laravel\Enums\GowaInstanceStatus;

class GowaInstanceResource extends Resource
{
    public static function getModel(): string
    {
        return config('gowa-filament.model', \Gowa\Laravel\Models\GowaInstance::class);
    }

    public static function getNavigationGroup(): ?string
    {
        return config('gowa-filament.navigation.group', 'WhatsApp');
    }

    public static function getNavigationIcon(): string
    {
        return config('gowa-filament.navigation.icon', 'heroicon-o-chat-bubble-left-right');
    }

    public static function getNavigationSort(): ?int
    {
        return config('gowa-filament.navigation.sort', 1);
    }

    public static function getNavigationLabel(): string
    {
        return __('gowa-filament::gowa-filament.navigation.label');
    }

    public static function getPluralModelLabel(): string
    {
        return __('gowa-filament::gowa-filament.navigation.plural_label');
    }

    public static function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('name')
                    ->label(__('gowa-filament::gowa-filament.fields.name'))
                    ->placeholder(__('gowa-filament::gowa-filament.fields.name_placeholder'))
                    ->helperText(__('gowa-filament::gowa-filament.fields.name_helper'))
                    ->prefixIcon('heroicon-o-tag')
                    ->required()
                    ->maxLength(255)
                    ->columnSpanFull(),

                TextInput::make('phone_number')
                    ->label(__('gowa-filament::gowa-filament.fields.phone'))
                    ->placeholder(__('gowa-filament::gowa-filament.fields.phone_placeholder'))
                    ->helperText(__('gowa-filament::gowa-filament.fields.phone_helper'))
                    ->prefixIcon('heroicon-o-phone')
                    ->tel()
                    ->maxLength(30)
                    ->columnSpanFull(),

                TextInput::make('device_id')
                    ->label(__('gowa-filament::gowa-filament.fields.device_id'))
                    ->placeholder(__('gowa-filament::gowa-filament.fields.device_id_placeholder'))
                    ->helperText(__('gowa-filament::gowa-filament.fields.device_id_helper'))
                    ->prefixIcon('heroicon-o-cpu-chip')
                    ->disabled()
                    ->hiddenOn(Operation::Create)
                    ->maxLength(255)
                    ->columnSpanFull(),

                TextInput::make('webhook_url')
                    ->label(__('gowa-filament::gowa-filament.fields.webhook_url'))
                    ->helperText(__('gowa-filament::gowa-filament.fields.webhook_url_helper'))
                    ->prefixIcon('heroicon-o-link')
                    ->formatStateUsing(fn ($record): ?string => $record ? url(config('gowa.webhook.path', 'webhooks/gowa') . '/' . $record->device_id) : null)
                    ->readOnly()
                    ->hiddenOn(Operation::Create)
                    ->maxLength(255)
                    ->columnSpanFull(),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                ImageColumn::make('avatar_url')
                    ->label('')
                    ->circular()
                    ->defaultImageUrl(fn ($record): string => 'https://ui-avatars.com/api/?name=' . urlencode($record->name ?? 'WA') . '&color=128C7E&background=DCF8C6')
                    ->getStateUsing(function ($record): ?string {
                        if (empty($record->phone_number) || empty($record->device_id)) {
                            return null;
                        }

                        return cache()->remember('gowa_avatar_' . $record->device_id, 86400, function () use ($record) {
                            try {
                                $avatar = \Gowa\Laravel\Facades\Gowa::avatar($record->device_id, $record->phone_number);
                                return $avatar?->url;
                            } catch (\Throwable $e) {
                                return null;
                            }
                        });
                    }),

                TextColumn::make('name')
                    ->label(__('gowa-filament::gowa-filament.fields.name'))
                    ->searchable()
                    ->sortable()
                    ->weight('bold'),

                TextColumn::make('device_id')
                    ->label(__('gowa-filament::gowa-filament.fields.device_id'))
                    ->searchable()
                    ->copyable()
                    ->fontFamily('mono'),

                TextColumn::make('webhook_url')
                    ->label(__('gowa-filament::gowa-filament.fields.webhook_url'))
                    ->getStateUsing(fn ($record): string => url(config('gowa.webhook.path', 'webhooks/gowa') . '/' . $record->device_id))
                    ->copyable()
                    ->fontFamily('mono')
                    ->toggleable(isToggledHiddenByDefault: false),

                TextColumn::make('phone_number')
                    ->label(__('gowa-filament::gowa-filament.fields.phone'))
                    ->searchable(),

                TextColumn::make('status')
                    ->label('Status')
                    ->badge()
                    ->color(fn (mixed $state): string => match ($state instanceof GowaInstanceStatus ? $state->value : (string) $state) {
                        'open', 'connected' => 'success',
                        'connecting' => 'warning',
                        'close', 'disconnected' => 'danger',
                        default => 'gray',
                    })
                    ->formatStateUsing(function (mixed $state): string {
                        $value = $state instanceof GowaInstanceStatus ? $state->value : (string) $state;

                        return match ($value) {
                            'open', 'connected' => __('gowa-filament::gowa-filament.status.connected'),
                            'connecting' => __('gowa-filament::gowa-filament.status.connecting'),
                            'close', 'disconnected' => __('gowa-filament::gowa-filament.status.disconnected'),
                            default => ucfirst($value),
                        };
                    }),

                TextColumn::make('connected_at')
                    ->label('Connected At')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),

                TextColumn::make('updated_at')
                    ->label('Last Activity')
                    ->dateTime()
                    ->sortable(),
            ])
            ->actions([
                ConnectQrAction::make()
                    ->visible(fn ($record): bool => ! ($record->status instanceof GowaInstanceStatus ? $record->status->isConnected() : in_array((string) $record->status, ['open', 'connected'], true))),

                ActionGroup::make([
                    SendGowaMessageAction::make('sendTestMessage')
                        ->label(__('gowa-filament::gowa-filament.actions.send_test_message'))
                        ->icon('heroicon-o-paper-airplane')
                        ->color('primary')
                        ->instanceFromRecord()
                        ->visible(fn ($record): bool => $record->status instanceof GowaInstanceStatus ? $record->status->isConnected() : in_array((string) $record->status, ['open', 'connected'], true)),

                    ConnectPairingCodeAction::make()
                        ->hidden(fn ($record): bool => $record->status instanceof GowaInstanceStatus ? $record->status->isConnected() : in_array((string) $record->status, ['open', 'connected'], true)),
                    RefreshStatusAction::make(),
                    DisconnectAction::make()
                        ->visible(fn ($record): bool => $record->status instanceof GowaInstanceStatus ? $record->status->isConnected() : in_array((string) $record->status, ['open', 'connected'], true)),
                    ViewAction::make()
                        ->modalHeading(__('gowa-filament::gowa-filament.actions.view_heading'))
                        ->modalDescription(__('gowa-filament::gowa-filament.actions.view_desc'))
                        ->modalWidth(Width::Medium),
                    EditAction::make()
                        ->modalHeading(__('gowa-filament::gowa-filament.actions.edit_heading'))
                        ->modalDescription(__('gowa-filament::gowa-filament.actions.edit_desc'))
                        ->modalWidth(Width::Medium),
                    DeleteAction::make(),
                ]),
            ])
            ->bulkActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => ListGowaInstances::route('/'),
        ];
    }
}
