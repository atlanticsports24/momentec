<?php

namespace App\Filament\Resources\ProductVariantResource\Pages;

use App\Filament\Concerns\HandlesCatalogImageUploads;
use App\Filament\Resources\ProductVariantResource;
use Filament\Resources\Pages\CreateRecord;

class CreateProductVariant extends CreateRecord
{
    use HandlesCatalogImageUploads;

    protected static string $resource = ProductVariantResource::class;

    protected function afterCreate(): void
    {
        $this->persistVariantImageUploads($this->record, $this->form->getState());
    }
}
