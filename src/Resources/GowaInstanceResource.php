<?php

namespace Gowa\Filament\Resources;

use Filament\Forms\Components\TextInput;
use Filament\Resources\Resource;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ViewAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
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
                Section::make(__('gowa-filament::gowa-filament.fields.section_title'))
                    ->description(__('gowa-filament::gowa-filament.fields.section_desc'))
                    ->icon('heroicon-o-chat-bubble-left-right')
                    ->schema([
                        TextInput::make('name')
                            ->label(__('gowa-filament::gowa-filament.fields.name'))
                            ->placeholder(__('gowa-filament::gowa-filament.fields.name_placeholder'))
                            ->helperText(__('gowa-filament::gowa-filament.fields.name_helper'))
                            ->prefixIcon('heroicon-o-tag')
                            ->required()
                            ->maxLength(255)
                            ->columnSpan(1),

                        TextInput::make('device_id')
                            ->label(__('gowa-filament::gowa-filament.fields.device_id'))
                            ->placeholder(__('gowa-filament::gowa-filament.fields.device_id_placeholder'))
                            ->helperText(__('gowa-filament::gowa-filament.fields.device_id_helper'))
                            ->prefixIcon('heroicon-o-cpu-chip')
                            ->required()
                            ->unique(ignoreRecord: true)
                            ->maxLength(255)
                            ->columnSpan(1),

                        TextInput::make('phone')
                            ->label(__('gowa-filament::gowa-filament.fields.phone'))
                            ->placeholder(__('gowa-filament::gowa-filament.fields.phone_placeholder'))
                            ->helperText(__('gowa-filament::gowa-filament.fields.phone_helper'))
                            ->prefixIcon('heroicon-o-phone')
                            ->tel()
                            ->maxLength(30)
                            ->columnSpan(1),

                        TextInput::make('jid')
                            ->label(__('gowa-filament::gowa-filament.fields.jid'))
                            ->placeholder(__('gowa-filament::gowa-filament.fields.jid_placeholder'))
                            ->helperText(__('gowa-filament::gowa-filament.fields.jid_helper'))
                            ->prefixIcon('heroicon-o-at-symbol')
                            ->disabled()
                            ->maxLength(255)
                            ->columnSpan(1),
                    ])->columns(2),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
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

                TextColumn::make('phone')
                    ->label(__('gowa-filament::gowa-filament.fields.phone'))
                    ->searchable(),

                TextColumn::make('status')
                    ->label('Status')
                    ->badge()
                    ->color(fn (string|GowaInstanceStatus $state): string => match ($state instanceof GowaInstanceStatus ? $state->value : $state) {
                        'open', 'connected' => 'success',
                        'connecting' => 'warning',
                        'close', 'disconnected' => 'danger',
                        default => 'gray',
                    })
                    ->formatStateUsing(fn (string|GowaInstanceStatus $state): string => match ($state instanceof GowaInstanceStatus ? $state->value : $state) {
                        'open', 'connected' => __('gowa-filament::gowa-filament.status.connected'),
                        'connecting' => __('gowa-filament::gowa-filament.status.connecting'),
                        'close', 'disconnected' => __('gowa-filament::gowa-filament.status.disconnected'),
                        default => ucfirst((string) $state),
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
                ConnectQrAction::make(),
                ConnectPairingCodeAction::make(),
                RefreshStatusAction::make(),
                DisconnectAction::make(),
                ViewAction::make(),
                EditAction::make(),
                DeleteAction::make(),
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
