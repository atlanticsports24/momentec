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
                Forms\Components\Toggle::make('is_enabled')->label('Status'),
                Forms\Components\TextInput::make('sort_order')->label('Sort order')->numeric()->default(0),
                Forms\Components\Select::make('geo_zone_id')
                    ->label('Geo zone')
                    ->relationship('geoZone', 'name')
                    ->placeholder('All zones')
                    ->helperText('Same as OpenCart “All Zones” when empty.'),
                Forms\Components\TextInput::make('cost')
                    ->numeric()
                    ->prefix('$')
                    ->visible(fn (?ShippingMethod $record): bool => ! in_array($record?->code, ['ups', 'usps'], true)),
                Forms\Components\TextInput::make('free_shipping_min')
                    ->label('Free shipping over')
                    ->numeric()
                    ->prefix('$')
                    ->visible(fn (?ShippingMethod $record): bool => ! in_array($record?->code, ['ups', 'usps'], true)),
                ...static::upsSchema(),
                ...static::uspsSchema(),
                Forms\Components\KeyValue::make('config')
                    ->columnSpanFull()
                    ->visible(fn (?ShippingMethod $record): bool => $record !== null && ! in_array($record->code, ['ups', 'usps'], true)),
            ]);
    }

    /**
     * @return array<int, Forms\Components\Component>
     */
    protected static function upsSchema(): array
    {
        $isUps = fn (?ShippingMethod $record): bool => $record?->code === 'ups';

        return [
            Forms\Components\Section::make('API credentials')
                ->description('OAuth Client ID / Secret are required for live rates. Username / Password are stored for reference (legacy OpenCart fields).')
                ->visible($isUps)
                ->columns(2)
                ->schema([
                    Forms\Components\TextInput::make('config.client_id')
                        ->label('Client ID')
                        ->columnSpanFull(),
                    Forms\Components\TextInput::make('config.client_secret')
                        ->label('Client secret')
                        ->password()
                        ->revealable()
                        ->columnSpanFull(),
                    Forms\Components\TextInput::make('config.username')
                        ->label('Username'),
                    Forms\Components\TextInput::make('config.password')
                        ->label('Password')
                        ->password()
                        ->revealable(),
                    Forms\Components\TextInput::make('config.shipper_number')
                        ->label('Shipper number')
                        ->default('0H25Y0'),
                ]),
            Forms\Components\Section::make('Pricing & surcharges')
                ->visible($isUps)
                ->columns(2)
                ->schema([
                    Forms\Components\TextInput::make('config.additional_charges')
                        ->label('Additional charges')
                        ->numeric()
                        ->prefix('$')
                        ->default(0)
                        ->helperText('OpenCart “Additional Charges” — used for UPS Ground (03) rules.'),
                    Forms\Components\TextInput::make('config.percentage_charges')
                        ->label('Percentage charges')
                        ->numeric()
                        ->suffix('%')
                        ->default(0)
                        ->helperText('OpenCart “Percentage Charges” — markup on returned rates.'),
                ]),
            Forms\Components\Section::make('Pickup, packaging & classification')
                ->visible($isUps)
                ->columns(2)
                ->schema([
                    Forms\Components\Select::make('config.pickup_method')
                        ->label('Pickup method')
                        ->options(config('ups.pickup_methods', []))
                        ->default('11')
                        ->searchable(),
                    Forms\Components\Select::make('config.packaging')
                        ->label('Packaging type')
                        ->options(config('ups.packaging_types', []))
                        ->default('02')
                        ->searchable(),
                    Forms\Components\Select::make('config.customer_classification')
                        ->label('Customer classification code')
                        ->options(config('ups.customer_classifications', []))
                        ->default('01'),
                    Forms\Components\Select::make('config.shipping_origin_code')
                        ->label('Shipping origin code')
                        ->options(config('ups.origin_codes', []))
                        ->default('US'),
                ]),
            Forms\Components\Section::make('Origin address')
                ->visible($isUps)
                ->columns(2)
                ->schema([
                    Forms\Components\TextInput::make('config.origin_city')->label('Origin city'),
                    Forms\Components\TextInput::make('config.origin_state')->label('Origin state / province'),
                    Forms\Components\TextInput::make('config.origin_country')
                        ->label('Origin country')
                        ->default('US')
                        ->maxLength(2),
                    Forms\Components\TextInput::make('config.origin_postcode')->label('Origin zip / postal code'),
                ]),
            Forms\Components\Section::make('Modes & quotes')
                ->visible($isUps)
                ->columns(2)
                ->schema([
                    Forms\Components\Toggle::make('config.test_mode')
                        ->label('Test mode')
                        ->default(true)
                        ->helperText('Uses UPS sandbox (CIE) URLs when enabled.'),
                    Forms\Components\Select::make('config.quote_type')
                        ->label('Quote type')
                        ->options(config('ups.quote_types', []))
                        ->default('residential'),
                    Forms\Components\Toggle::make('config.enable_insurance')
                        ->label('Enable insurance')
                        ->default(false),
                    Forms\Components\Toggle::make('config.display_delivery_weight')
                        ->label('Display delivery weight')
                        ->default(true),
                    Forms\Components\Toggle::make('config.debug_mode')
                        ->label('Debug mode')
                        ->default(false)
                        ->helperText('Logs UPS API request/response details.'),
                ]),
            Forms\Components\Section::make('Package & dimensions')
                ->visible($isUps)
                ->columns(3)
                ->schema([
                    Forms\Components\Select::make('config.weight_unit')
                        ->label('Weight class')
                        ->options(config('ups.weight_classes', []))
                        ->default('LBS'),
                    Forms\Components\Select::make('config.length_unit')
                        ->label('Length class')
                        ->options(config('ups.length_classes', []))
                        ->default('IN'),
                    Forms\Components\Placeholder::make('dim_spacer')->label(''),
                    Forms\Components\TextInput::make('config.length')
                        ->label('Length')
                        ->numeric()
                        ->default(5),
                    Forms\Components\TextInput::make('config.width')
                        ->label('Width')
                        ->numeric()
                        ->default(5),
                    Forms\Components\TextInput::make('config.height')
                        ->label('Height')
                        ->numeric()
                        ->default(5),
                ]),
            Forms\Components\Section::make('Services')
                ->description('Select which UPS services may appear at checkout (OpenCart services list).')
                ->visible($isUps)
                ->schema([
                    Forms\Components\CheckboxList::make('config.enabled_services')
                        ->label('Services')
                        ->options(config('ups.services', []))
                        ->columns(2)
                        ->default(['01', '02', '03']),
                ]),
            Forms\Components\Section::make('Tax')
                ->visible($isUps)
                ->schema([
                    Forms\Components\Placeholder::make('tax_note')
                        ->label('Tax class')
                        ->content('Tax is calculated from Localisation → Zones and Store Settings → Enable tax (replaces OpenCart tax class on shipping).'),
                ]),
        ];
    }

    /**
     * @return array<int, Forms\Components\Component>
     */
    protected static function uspsSchema(): array
    {
        $isUsps = fn (?ShippingMethod $record): bool => $record?->code === 'usps';

        return [
            Forms\Components\Section::make('USPS API (RateV4)')
                ->description('OpenCart default USPS module — uses USPS Shipping API with your Web Tools User ID.')
                ->visible($isUsps)
                ->columns(2)
                ->schema([
                    Forms\Components\TextInput::make('config.user_id')
                        ->label('User ID')
                        ->required()
                        ->columnSpanFull()
                        ->helperText('From https://www.usps.com/business/web-tools-apis/'),
                    Forms\Components\TextInput::make('config.origin_postcode')
                        ->label('Origin zip / postal code')
                        ->required(),
                    Forms\Components\Toggle::make('config.test_mode')
                        ->label('Test mode')
                        ->default(true),
                    Forms\Components\Toggle::make('config.display_delivery_weight')
                        ->label('Display delivery weight')
                        ->default(true),
                    Forms\Components\Toggle::make('config.debug_mode')
                        ->label('Debug mode')
                        ->default(false),
                ]),
            Forms\Components\Section::make('USPS pricing & package')
                ->visible($isUsps)
                ->columns(2)
                ->schema([
                    Forms\Components\TextInput::make('config.additional_charges')
                        ->label('Additional charges')
                        ->numeric()
                        ->prefix('$')
                        ->default(0),
                    Forms\Components\TextInput::make('config.percentage_charges')
                        ->label('Percentage charges')
                        ->numeric()
                        ->suffix('%')
                        ->default(0),
                    Forms\Components\Select::make('config.container')
                        ->label('Container')
                        ->options(config('usps.containers', []))
                        ->default('VARIABLE')
                        ->searchable(),
                    Forms\Components\TextInput::make('config.length')
                        ->label('Length')
                        ->numeric()
                        ->default(5),
                    Forms\Components\TextInput::make('config.width')
                        ->label('Width')
                        ->numeric()
                        ->default(5),
                    Forms\Components\TextInput::make('config.height')
                        ->label('Height')
                        ->numeric()
                        ->default(5),
                ]),
            Forms\Components\Section::make('USPS services')
                ->visible($isUsps)
                ->schema([
                    Forms\Components\CheckboxList::make('config.enabled_services')
                        ->label('Services')
                        ->options(config('usps.services', []))
                        ->columns(2)
                        ->default(['PRIORITY MAIL', 'PRIORITY MAIL EXPRESS', 'FIRST-CLASS MAIL']),
                ]),
            Forms\Components\Section::make('Tax')
                ->visible($isUsps)
                ->schema([
                    Forms\Components\Placeholder::make('usps_tax_note')
                        ->label('Tax class')
                        ->content('Use Store Settings and zone tax rates (replaces OpenCart shipping tax class).'),
                ]),
        ];
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('code'),
                Tables\Columns\TextColumn::make('name'),
                Tables\Columns\IconColumn::make('is_enabled')->label('Status')->boolean(),
                Tables\Columns\TextColumn::make('sort_order')->label('Sort'),
                Tables\Columns\TextColumn::make('geoZone.name')->label('Geo zone')->placeholder('All'),
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
