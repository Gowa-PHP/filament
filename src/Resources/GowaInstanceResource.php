<?php

namespace Gowa\Filament\Resources;

use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Resources\Resource;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Filament\Tables\Actions\BulkActionGroup;
use Filament\Tables\Actions\DeleteAction;
use Filament\Tables\Actions\DeleteBulkAction;
use Filament\Tables\Actions\EditAction;
use Filament\Tables\Actions\ViewAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Gowa\Filament\Resources\GowaInstanceResource\Actions\ConnectPairingCodeAction;
use Gowa\Filament\Resources\GowaInstanceResource\Actions\ConnectQrAction;
use Gowa\Filament\Resources\GowaInstanceResource\Actions\DisconnectAction;
use Gowa\Filament\Resources\GowaInstanceResource\Actions\RefreshStatusAction;
use Gowa\Filament\Resources\GowaInstanceResource\Pages\CreateGowaInstance;
use Gowa\Filament\Resources\GowaInstanceResource\Pages\EditGowaInstance;
use Gowa\Filament\Resources\GowaInstanceResource\Pages\ListGowaInstances;
use Gowa\Filament\Resources\GowaInstanceResource\Pages\ViewGowaInstance;
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
                Section::make('Instance Information')
                    ->schema([
                        TextInput::make('name')
                            ->label('Name / Alias')
                            ->placeholder('e.g. Sales WhatsApp')
                            ->required()
                            ->maxLength(255),

                        TextInput::make('device_id')
                            ->label('Device ID')
                            ->placeholder('e.g. device_01')
                            ->required()
                            ->unique(ignoreRecord: true)
                            ->maxLength(255),

                        TextInput::make('phone')
                            ->label('Phone Number')
                            ->placeholder('e.g. 5511999999999')
                            ->maxLength(30),

                        TextInput::make('jid')
                            ->label('WhatsApp JID')
                            ->placeholder('e.g. 5511999999999@s.whatsapp.net')
                            ->maxLength(255),

                        Select::make('status')
                            ->label('Status')
                            ->options([
                                GowaInstanceStatus::Open->value => __('gowa-filament::gowa-filament.status.connected'),
                                GowaInstanceStatus::Connecting->value => __('gowa-filament::gowa-filament.status.connecting'),
                                GowaInstanceStatus::Close->value => __('gowa-filament::gowa-filament.status.disconnected'),
                                GowaInstanceStatus::Created->value => 'Created',
                            ])
                            ->default(GowaInstanceStatus::Close->value)
                            ->required(),
                    ])->columns(2),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('name')
                    ->label('Name')
                    ->searchable()
                    ->sortable()
                    ->weight('bold'),

                TextColumn::make('device_id')
                    ->label('Device ID')
                    ->searchable()
                    ->copyable()
                    ->fontFamily('mono'),

                TextColumn::make('phone')
                    ->label('Phone')
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
            'create' => CreateGowaInstance::route('/create'),
            'view' => ViewGowaInstance::route('/{record}'),
            'edit' => EditGowaInstance::route('/{record}/edit'),
        ];
    }
}
