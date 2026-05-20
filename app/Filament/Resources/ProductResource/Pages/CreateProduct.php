<?php

namespace App\Filament\Resources\ProductResource\Pages;

use App\Filament\Concerns\HandlesCatalogImageUploads;
use App\Filament\Resources\ProductResource;
use Filament\Resources\Pages\CreateRecord;

class CreateProduct extends CreateRecord
{
    use HandlesCatalogImageUploads;

    protected static string $resource = ProductResource::class;

    protected function afterCreate(): void
    {
        $this->persistProductMainImageUpload($this->record, $this->form->getState());
    }
}
