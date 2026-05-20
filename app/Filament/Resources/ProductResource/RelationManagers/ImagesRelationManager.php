<?php

namespace App\Filament\Resources\ProductResource\RelationManagers;

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
                        'gallery' => 'Gallery',
                        'swatch' => 'Swatch',
                        'size_chart' => 'Size chart',
                    ])
                    ->required()
                    ->default('gallery'),
                Forms\Components\FileUpload::make('path')
                    ->label('Image file')
                    ->disk('public')
                    ->directory('products/manual')
                    ->image()
                    ->required(),
                Forms\Components\TextInput::make('source_url')
                    ->label('Original URL (optional)')
                    ->url()
                    ->maxLength(2048),
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
                Tables\Columns\TextColumn::make('path')->limit(40)->toggleable(),
                Tables\Columns\TextColumn::make('source_url')->limit(30)->toggleable(isToggledHiddenByDefault: true),
            ])
            ->headerActions([
                Tables\Actions\CreateAction::make()
                    ->mutateFormDataUsing(function (array $data): array {
                        $data['download_status'] = Image::STATUS_COMPLETED;
                        $data['disk'] = CatalogImageStorage::DISK;
                        $data['product_variant_id'] = null;

                        return $data;
                    })
                    ->after(function (Image $record): void {
                        if ($record->role === 'main' && filled($record->path)) {
                            app(CatalogImageStorage::class)->setProductMainImage(
                                $this->getOwnerRecord(),
                                $record->path
                            );
                        }
                    }),
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make(),
            ]);
    }
}
