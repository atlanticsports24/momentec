<?php

namespace App\Filament\Resources\ProductVariantDisplayOptionResource\Pages;

use App\Filament\Resources\ProductVariantDisplayOptionResource;
use Filament\Actions;
use Filament\Resources\Pages\ViewRecord;

class ViewProductVariantDisplayOption extends ViewRecord
{
    protected static string $resource = ProductVariantDisplayOptionResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\EditAction::make(),
        ];
    }
}
