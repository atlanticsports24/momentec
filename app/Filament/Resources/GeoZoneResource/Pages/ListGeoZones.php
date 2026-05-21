<?php

namespace App\Filament\Resources\GeoZoneResource\Pages;

use App\Filament\Resources\GeoZoneResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListGeoZones extends ListRecords
{
    protected static string $resource = GeoZoneResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
