<?php

namespace App\Filament\Resources\ProductVariantDisplayOptionResource\Pages;

use App\Filament\Resources\ProductVariantDisplayOptionResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListProductVariantDisplayOptions extends ListRecords
{
    protected static string $resource = ProductVariantDisplayOptionResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
