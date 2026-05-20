<?php

namespace App\Filament\Resources;

use App\Filament\Resources\ProductVariantDisplayOptionResource\Pages;
use App\Filament\Resources\ProductVariantDisplayOptionResource\RelationManagers;
use App\Models\ProductVariantDisplayOption;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class ProductVariantDisplayOptionResource extends Resource
{
    protected static ?string $model = ProductVariantDisplayOption::class;

    protected static ?string $navigationIcon = 'heroicon-o-adjustments-horizontal';

    protected static ?string $navigationGroup = 'Catalog';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Select::make('product_id')
                    ->relationship('product', 'name')
                    ->required(),
                Forms\Components\Select::make('dimension')
                    ->options([
                        'color' => 'Color',
                        'size' => 'Size',
                    ])
                    ->required(),
                Forms\Components\Select::make('display_type')
                    ->options([
                        'select' => 'Select',
                        'radio' => 'Radio',
                        'swatch' => 'Swatch',
                    ])
                    ->required(),
                Forms\Components\TextInput::make('sort_order')
                    ->required()
                    ->numeric()
                    ->default(0),
                Forms\Components\TextInput::make('label'),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('product.name')
                    ->sortable(),
                Tables\Columns\TextColumn::make('dimension')
                    ->searchable(),
                Tables\Columns\TextColumn::make('display_type')
                    ->searchable(),
                Tables\Columns\TextColumn::make('sort_order')
                    ->numeric()
                    ->sortable(),
                Tables\Columns\TextColumn::make('label')
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
            //
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListProductVariantDisplayOptions::route('/'),
            'create' => Pages\CreateProductVariantDisplayOption::route('/create'),
            'view' => Pages\ViewProductVariantDisplayOption::route('/{record}'),
            'edit' => Pages\EditProductVariantDisplayOption::route('/{record}/edit'),
        ];
    }
}
