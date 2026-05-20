<?php

namespace App\Filament\Resources\ProductVariantDisplayOptionResource\Pages;

use App\Filament\Resources\ProductVariantDisplayOptionResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditProductVariantDisplayOption extends EditRecord
{
    protected static string $resource = ProductVariantDisplayOptionResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\ViewAction::make(),
            Actions\DeleteAction::make(),
        ];
    }
}
