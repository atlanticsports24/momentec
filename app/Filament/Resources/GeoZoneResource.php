<?php

namespace App\Filament\Resources;

use App\Filament\Resources\GeoZoneResource\Pages;
use App\Filament\Resources\GeoZoneResource\RelationManagers;
use App\Models\GeoZone;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class GeoZoneResource extends Resource
{
    protected static ?string $model = GeoZone::class;

    protected static ?string $navigationIcon = 'heroicon-o-globe-alt';

    protected static ?string $navigationGroup = 'Localisation';

    protected static ?int $navigationSort = 3;

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\TextInput::make('name')->required()->maxLength(255),
                Forms\Components\Textarea::make('description')->columnSpanFull(),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('name')->searchable(),
                Tables\Columns\TextColumn::make('description')->limit(50),
                Tables\Columns\TextColumn::make('zones_count')->counts('zones')->label('Zones'),
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
            ]);
    }

    public static function getRelations(): array
    {
        return [
            RelationManagers\ZonesRelationManager::class,
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListGeoZones::route('/'),
            'create' => Pages\CreateGeoZone::route('/create'),
            'edit' => Pages\EditGeoZone::route('/{record}/edit'),
        ];
    }
}
