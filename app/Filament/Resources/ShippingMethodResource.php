<?php

namespace App\Filament\Resources;

use App\Filament\Resources\ShippingMethodResource\Pages;
use App\Models\ShippingMethod;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class ShippingMethodResource extends Resource
{
    protected static ?string $model = ShippingMethod::class;

    protected static ?string $navigationIcon = 'heroicon-o-truck';

    protected static ?string $navigationGroup = 'Extensions';

    protected static ?int $navigationSort = 2;

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\TextInput::make('code')
                    ->required()
                    ->disabledOn('edit'),
                Forms\Components\TextInput::make('name')->required(),
                Forms\Components\Toggle::make('is_enabled')->default(false),
                Forms\Components\TextInput::make('sort_order')->numeric()->default(0),
                Forms\Components\Select::make('geo_zone_id')
                    ->relationship('geoZone', 'name')
                    ->helperText('Leave empty for all locations.'),
                Forms\Components\TextInput::make('cost')->numeric()->prefix('$')->default(0),
                Forms\Components\TextInput::make('free_shipping_min')
                    ->label('Free shipping over')
                    ->numeric()
                    ->prefix('$'),
                Forms\Components\KeyValue::make('config')->columnSpanFull(),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('code'),
                Tables\Columns\TextColumn::make('name'),
                Tables\Columns\IconColumn::make('is_enabled')->boolean(),
                Tables\Columns\TextColumn::make('cost')->money('USD'),
                Tables\Columns\TextColumn::make('geoZone.name')->placeholder('All'),
            ])
            ->defaultSort('sort_order')
            ->actions([Tables\Actions\EditAction::make()]);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListShippingMethods::route('/'),
            'edit' => Pages\EditShippingMethod::route('/{record}/edit'),
        ];
    }

    public static function canCreate(): bool
    {
        return false;
    }
}
