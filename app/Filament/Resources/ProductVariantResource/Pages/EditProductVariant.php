<?php

namespace App\Filament\Resources\ProductVariantResource\Pages;

use App\Filament\Concerns\HandlesCatalogImageUploads;
use App\Filament\Resources\ProductVariantResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditProductVariant extends EditRecord
{
    use HandlesCatalogImageUploads;

    protected static string $resource = ProductVariantResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\ViewAction::make(),
            Actions\DeleteAction::make(),
        ];
    }

    protected function afterSave(): void
    {
        $this->persistVariantImageUploads($this->record, $this->form->getState());
    }
}
