<?php

namespace App\Filament\Resources;

use App\Filament\Resources\ProductResource\Pages;
use App\Filament\Resources\ProductResource\RelationManagers;
use App\Filament\Support\CatalogImageForm;
use App\Models\Product;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class ProductResource extends Resource
{
    protected static ?string $model = Product::class;

    protected static ?string $navigationIcon = 'heroicon-o-rectangle-stack';

    protected static ?string $navigationGroup = 'Catalog';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\TextInput::make('parent_sku')
                    ->required(),
                Forms\Components\Select::make('brand_id')
                    ->relationship('brand', 'name')
                    ->searchable()
                    ->preload(),
                Forms\Components\TextInput::make('name')
                    ->required(),
                Forms\Components\Textarea::make('description')
                    ->columnSpanFull(),
                Forms\Components\Toggle::make('admin_description_locked')
                    ->label('Lock description from feed overwrites'),
                Forms\Components\TextInput::make('division'),
                Forms\Components\TextInput::make('currency')
                    ->default('USD')
                    ->maxLength(8),
                Forms\Components\TextInput::make('variation_theme'),
                Forms\Components\DatePicker::make('launch_date'),
                Forms\Components\Textarea::make('features')
                    ->columnSpanFull(),
                Forms\Components\TextInput::make('min_msrp')
                    ->numeric()
                    ->disabled()
                    ->dehydrated(false)
                    ->helperText('Recomputed after variant sync.'),
                Forms\Components\TextInput::make('max_msrp')
                    ->numeric()
                    ->disabled()
                    ->dehydrated(false),
                Forms\Components\TextInput::make('min_cost')
                    ->numeric()
                    ->disabled()
                    ->dehydrated(false),
                Forms\Components\TextInput::make('max_cost')
                    ->numeric()
                    ->disabled()
                    ->dehydrated(false),
                CatalogImageForm::productMainImageSection(),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\ImageColumn::make('main_image')
                    ->label('Image')
                    ->getStateUsing(fn (Product $record): ?string => $record->mainImageRelativePath() ?? $record->mainImageUrl())
                    ->disk(fn (?string $state): ?string => $state && ! str_contains($state, '://') ? 'public' : null)
                    ->height(56)
                    ->square(),
                Tables\Columns\TextColumn::make('parent_sku')
                    ->searchable(),
                Tables\Columns\TextColumn::make('brand.name')
                    ->sortable(),
                Tables\Columns\TextColumn::make('name')
                    ->searchable(),
                Tables\Columns\IconColumn::make('admin_description_locked')
                    ->boolean(),
                Tables\Columns\TextColumn::make('division')
                    ->searchable(),
                Tables\Columns\TextColumn::make('currency')
                    ->searchable(),
                Tables\Columns\TextColumn::make('variation_theme')
                    ->searchable(),
                Tables\Columns\TextColumn::make('launch_date')
                    ->date()
                    ->sortable(),
                Tables\Columns\TextColumn::make('min_msrp')
                    ->numeric()
                    ->sortable(),
                Tables\Columns\TextColumn::make('max_msrp')
                    ->numeric()
                    ->sortable(),
                Tables\Columns\TextColumn::make('min_cost')
                    ->numeric()
                    ->sortable(),
                Tables\Columns\TextColumn::make('max_cost')
                    ->numeric()
                    ->sortable(),
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
            RelationManagers\VariantsRelationManager::class,
            RelationManagers\VariantDisplayOptionsRelationManager::class,
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListProducts::route('/'),
            'create' => Pages\CreateProduct::route('/create'),
            'view' => Pages\ViewProduct::route('/{record}'),
            'edit' => Pages\EditProduct::route('/{record}/edit'),
        ];
    }
}
