<?php

namespace App\Filament\Resources;

use App\Filament\Resources\ProductVariantResource\Pages;
use App\Filament\Resources\ProductVariantResource\RelationManagers;
use App\Filament\Support\CatalogImageForm;
use App\Models\ProductVariant;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class ProductVariantResource extends Resource
{
    protected static ?string $model = ProductVariant::class;

    protected static ?string $navigationIcon = 'heroicon-o-squares-2x2';

    protected static ?string $navigationGroup = 'Catalog';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Select::make('product_id')
                    ->relationship('product', 'name')
                    ->required(),
                Forms\Components\TextInput::make('item_sku')
                    ->required(),
                Forms\Components\TextInput::make('upc_code'),
                Forms\Components\TextInput::make('gtin'),
                Forms\Components\TextInput::make('msrp')
                    ->numeric(),
                Forms\Components\TextInput::make('cost')
                    ->numeric()
                    ->prefix('$'),
                Forms\Components\TextInput::make('currency')
                    ->required(),
                Forms\Components\TextInput::make('division'),
                Forms\Components\Textarea::make('item_description')
                    ->columnSpanFull(),
                Forms\Components\Toggle::make('admin_variant_description_locked')
                    ->label('Lock description from feed overwrites'),
                CatalogImageForm::variantImagesSection(),
                Forms\Components\Section::make('Feed image URLs (from import)')
                    ->schema([
                        Forms\Components\TextInput::make('main_image_url')->label('Main URL')->url()->maxLength(2048),
                        Forms\Components\TextInput::make('swatch_image_url')->label('Swatch URL')->url()->maxLength(2048),
                        Forms\Components\TextInput::make('other_image_url')->label('Other URL')->url()->maxLength(2048),
                        Forms\Components\TextInput::make('size_chart_image_url')->label('Size chart URL')->url()->maxLength(2048),
                    ])
                    ->columns(2)
                    ->collapsed(),
                Forms\Components\TextInput::make('variation_theme'),
                Forms\Components\TextInput::make('color'),
                Forms\Components\TextInput::make('size'),
                Forms\Components\TextInput::make('weight')
                    ->numeric(),
                Forms\Components\TextInput::make('weight_unit'),
                Forms\Components\TextInput::make('volume')
                    ->numeric(),
                Forms\Components\TextInput::make('volume_unit'),
                Forms\Components\TextInput::make('case_pack_qty')
                    ->numeric(),
                Forms\Components\TextInput::make('color_hex_value'),
                Forms\Components\TextInput::make('status'),
                Forms\Components\Textarea::make('product_video_url')
                    ->columnSpanFull(),
                Forms\Components\TextInput::make('ribbon'),
                Forms\Components\TextInput::make('country_of_origin'),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\ImageColumn::make('main_image')
                    ->label('Image')
                    ->getStateUsing(fn (ProductVariant $record): ?string => $record->mainImageRelativePath() ?? $record->mainImageUrl())
                    ->disk(fn (?string $state): ?string => $state && ! str_contains($state, '://') ? 'public' : null)
                    ->height(56)
                    ->square(),
                Tables\Columns\TextColumn::make('product.name')
                    ->sortable(),
                Tables\Columns\TextColumn::make('item_sku')
                    ->searchable(),
                Tables\Columns\TextColumn::make('upc_code')
                    ->searchable(),
                Tables\Columns\TextColumn::make('gtin')
                    ->searchable(),
                Tables\Columns\TextColumn::make('msrp')
                    ->numeric()
                    ->sortable(),
                Tables\Columns\TextColumn::make('cost')
                    ->numeric()
                    ->sortable(),
                Tables\Columns\TextColumn::make('currency')
                    ->searchable(),
                Tables\Columns\TextColumn::make('division')
                    ->searchable(),
                Tables\Columns\IconColumn::make('admin_variant_description_locked')
                    ->boolean(),
                Tables\Columns\TextColumn::make('main_image_url')
                    ->limit(32)
                    ->toggleable(isToggledHiddenByDefault: true),
                Tables\Columns\TextColumn::make('color')
                    ->searchable(),
                Tables\Columns\TextColumn::make('size')
                    ->searchable(),
                Tables\Columns\TextColumn::make('swatch_image_url')
                    ->limit(24)
                    ->toggleable(isToggledHiddenByDefault: true),
                Tables\Columns\TextColumn::make('size_chart_image_url')
                    ->limit(24)
                    ->toggleable(isToggledHiddenByDefault: true),
                Tables\Columns\TextColumn::make('variation_theme')
                    ->searchable(),
                Tables\Columns\TextColumn::make('weight')
                    ->numeric()
                    ->sortable(),
                Tables\Columns\TextColumn::make('weight_unit')
                    ->searchable(),
                Tables\Columns\TextColumn::make('volume')
                    ->numeric()
                    ->sortable(),
                Tables\Columns\TextColumn::make('volume_unit')
                    ->searchable(),
                Tables\Columns\TextColumn::make('case_pack_qty')
                    ->numeric()
                    ->sortable(),
                Tables\Columns\TextColumn::make('color_hex_value')
                    ->searchable(),
                Tables\Columns\TextColumn::make('status')
                    ->searchable(),
                Tables\Columns\TextColumn::make('ribbon')
                    ->searchable(),
                Tables\Columns\TextColumn::make('country_of_origin')
                    ->searchable(),
                Tables\Columns\TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                Tables\Columns\TextColumn::make('updated_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                //
            ])
            ->actions([
                Tables\Actions\ViewAction::make(),
                Tables\Actions\EditAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ]);
    }

    public static function getRelations(): array
    {
        return [
            RelationManagers\ImagesRelationManager::class,
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListProductVariants::route('/'),
            'create' => Pages\CreateProductVariant::route('/create'),
            'view' => Pages\ViewProductVariant::route('/{record}'),
            'edit' => Pages\EditProductVariant::route('/{record}/edit'),
        ];
    }
}
