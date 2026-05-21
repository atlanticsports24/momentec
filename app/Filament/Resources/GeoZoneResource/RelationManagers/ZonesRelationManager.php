<?php

namespace App\Filament\Resources\GeoZoneResource\RelationManagers;

use App\Models\Zone;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables;
use Filament\Tables\Table;

class ZonesRelationManager extends RelationManager
{
    protected static string $relationship = 'zones';

    public function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('country.name')->label('Country'),
                Tables\Columns\TextColumn::make('name')->label('Zone'),
                Tables\Columns\TextColumn::make('code'),
            ])
            ->headerActions([
                Tables\Actions\AttachAction::make()
                    ->preloadRecordSelect()
                    ->recordTitle(fn (Zone $record): string => $record->country->name.' — '.$record->name.' ('.$record->code.')')
                    ->using(function (Tables\Actions\AttachAction $action, Zone $record): void {
                        $action->getRelationship()->attach($record->getKey(), [
                            'country_id' => $record->country_id,
                        ]);
                    }),
            ])
            ->actions([
                Tables\Actions\DetachAction::make(),
            ]);
    }
}
