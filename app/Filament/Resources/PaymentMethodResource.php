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
                Forms\Components\Toggle::make('is_enabled')->default(false),
                Forms\Components\TextInput::make('sort_order')->numeric()->default(0),
                Forms\Components\Select::make('geo_zone_id')
                    ->relationship('geoZone', 'name')
                    ->helperText('Leave empty for all locations.'),
                Forms\Components\TextInput::make('min_total')->numeric()->prefix('$'),
                Forms\Components\TextInput::make('max_total')->numeric()->prefix('$'),
                Forms\Components\Select::make('success_order_status_id')
                    ->label('Order status after successful payment')
                    ->relationship('successOrderStatus', 'name')
                    ->searchable()
                    ->preload(),
                Forms\Components\Select::make('failed_order_status_id')
                    ->relationship('failedOrderStatus', 'name')
                    ->searchable()
                    ->preload(),
                Forms\Components\KeyValue::make('config')
                    ->columnSpanFull()
                    ->helperText('Gateway credentials and options (Stripe keys, Authorize.Net login, etc.).'),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('code'),
                Tables\Columns\TextColumn::make('name')->searchable(),
                Tables\Columns\IconColumn::make('is_enabled')->boolean(),
                Tables\Columns\TextColumn::make('sort_order')->sortable(),
                Tables\Columns\TextColumn::make('successOrderStatus.name')->label('Success status'),
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
