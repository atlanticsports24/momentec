<?php

namespace App\Filament\Resources;

use App\Filament\Resources\PaymentMethodResource\Pages;
use App\Models\PaymentMethod;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class PaymentMethodResource extends Resource
{
    protected static ?string $model = PaymentMethod::class;

    protected static ?string $navigationIcon = 'heroicon-o-credit-card';

    protected static ?string $navigationGroup = 'Extensions';

    protected static ?int $navigationSort = 1;

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\TextInput::make('code')
                    ->required()
                    ->maxLength(64)
                    ->disabledOn('edit'),
                Forms\Components\TextInput::make('name')->required(),
                Forms\Components\Toggle::make('is_enabled')
                    ->label('Status')
                    ->helperText('Enabled methods appear at checkout when geo/total rules match.'),
                Forms\Components\TextInput::make('sort_order')->numeric()->default(0),
                Forms\Components\Select::make('geo_zone_id')
                    ->label('Geo zone')
                    ->relationship('geoZone', 'name')
                    ->helperText('Leave empty for all zones (like OpenCart “All Zones”).'),
                Forms\Components\TextInput::make('min_total')
                    ->label('Total')
                    ->numeric()
                    ->prefix('$')
                    ->helperText('Minimum order subtotal required before this method is offered.'),
                Forms\Components\TextInput::make('max_total')->numeric()->prefix('$'),
                Forms\Components\Select::make('success_order_status_id')
                    ->label('Order status')
                    ->helperText('Status after successful payment (OpenCart “Order Status”).')
                    ->relationship('successOrderStatus', 'name')
                    ->searchable()
                    ->preload(),
                Forms\Components\Select::make('failed_order_status_id')
                    ->relationship('failedOrderStatus', 'name')
                    ->searchable()
                    ->preload(),
                Forms\Components\Section::make('Authorize.Net (AIM)')
                    ->description('Same fields as OpenCart Authorize.Net AIM extension.')
                    ->visible(fn (?PaymentMethod $record): bool => $record?->code === 'authorize_net')
                    ->columns(2)
                    ->schema([
                        Forms\Components\TextInput::make('config.api_login_id')
                            ->label('Login ID')
                            ->required()
                            ->maxLength(255),
                        Forms\Components\TextInput::make('config.transaction_key')
                            ->label('Transaction key')
                            ->password()
                            ->revealable()
                            ->required()
                            ->maxLength(255),
                        Forms\Components\TextInput::make('config.md5_hash')
                            ->label('MD5 hash')
                            ->maxLength(255)
                            ->columnSpanFull(),
                        Forms\Components\Select::make('config.server')
                            ->label('Transaction server')
                            ->options([
                                'live' => 'Live',
                                'test' => 'Test',
                            ])
                            ->default('test')
                            ->required(),
                        Forms\Components\Select::make('config.mode')
                            ->label('Transaction mode')
                            ->options([
                                'live' => 'Live',
                                'test' => 'Test',
                            ])
                            ->default('test')
                            ->required()
                            ->helperText('Test mode sends x_test_request to the gateway.'),
                        Forms\Components\Select::make('config.method')
                            ->label('Transaction method')
                            ->options([
                                'capture' => 'Payment (AUTH_CAPTURE)',
                                'authorization' => 'Authorization (AUTH_ONLY)',
                            ])
                            ->default('capture')
                            ->required(),
                    ]),
                Forms\Components\KeyValue::make('config')
                    ->columnSpanFull()
                    ->visible(fn (?PaymentMethod $record): bool => $record !== null && $record->code !== 'authorize_net')
                    ->helperText('Gateway credentials and options.'),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('code'),
                Tables\Columns\TextColumn::make('name')->searchable(),
                Tables\Columns\IconColumn::make('is_enabled')->label('Status')->boolean(),
                Tables\Columns\TextColumn::make('sort_order')->sortable(),
                Tables\Columns\TextColumn::make('successOrderStatus.name')->label('Order status'),
                Tables\Columns\TextColumn::make('geoZone.name')->label('Geo zone')->placeholder('All'),
            ])
            ->defaultSort('sort_order')
            ->actions([Tables\Actions\EditAction::make()]);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListPaymentMethods::route('/'),
            'edit' => Pages\EditPaymentMethod::route('/{record}/edit'),
        ];
    }

    public static function canCreate(): bool
    {
        return false;
    }
}
