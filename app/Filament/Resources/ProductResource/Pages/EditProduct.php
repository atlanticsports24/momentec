<?php

namespace App\Filament\Resources\ProductResource\Pages;

use App\Filament\Concerns\HandlesCatalogImageUploads;
use App\Filament\Resources\ProductResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditProduct extends EditRecord
{
    use HandlesCatalogImageUploads;

    protected static string $resource = ProductResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\ViewAction::make(),
            Actions\DeleteAction::make(),
        ];
    }

    protected function afterSave(): void
    {
        $this->persistProductMainImageUpload($this->record, $this->form->getState());
    }
}
