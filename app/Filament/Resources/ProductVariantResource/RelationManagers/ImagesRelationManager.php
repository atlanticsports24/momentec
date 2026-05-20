<?php

namespace App\Filament\Resources\ProductVariantResource\RelationManagers;

use App\Models\Image;
use App\Services\Catalog\CatalogImageStorage;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables;
use Filament\Tables\Table;

class ImagesRelationManager extends RelationManager
{
    protected static string $relationship = 'images';

    protected static ?string $title = 'Images';

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Select::make('role')
                    ->options([
                        'main' => 'Main',
                        'swatch' => 'Swatch',
                        'other' => 'Other',
                        'size_chart' => 'Size chart',
                    ])
                    ->required(),
                Forms\Components\FileUpload::make('path')
                    ->label('Image file')
                    ->disk('public')
                    ->directory('products/variants')
                    ->image()
                    ->required(),
            ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('role')
            ->columns([
                Tables\Columns\ImageColumn::make('path')
                    ->label('Preview')
                    ->disk('public')
                    ->getStateUsing(fn (Image $record): ?string => $record->storagePath()),
                Tables\Columns\TextColumn::make('role')->badge(),
                Tables\Columns\TextColumn::make('download_status')->badge(),
                Tables\Columns\TextColumn::make('path')->limit(40),
            ])
            ->headerActions([
                Tables\Actions\CreateAction::make()
                    ->mutateFormDataUsing(function (array $data): array {
                        $data['download_status'] = Image::STATUS_COMPLETED;
                        $data['disk'] = CatalogImageStorage::DISK;
                        $data['product_id'] = $this->getOwnerRecord()->product_id;

                        return $data;
                    })
                    ->after(function (Image $record): void {
                        app(CatalogImageStorage::class)->setVariantImage(
                            $this->getOwnerRecord(),
                            $record->path,
                            $record->role
                        );
                    }),
            ])
            ->actions([
                Tables\Actions\DeleteAction::make(),
            ]);
    }
}
