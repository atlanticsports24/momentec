<?php

namespace App\Filament\Resources\ProductResource\RelationManagers;

use App\Models\ProductVariant;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables;
use Filament\Tables\Table;

class VariantsRelationManager extends RelationManager
{
    protected static string $relationship = 'variants';

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\TextInput::make('item_sku')
                    ->required()
                    ->maxLength(255),
                Forms\Components\TextInput::make('upc_code'),
                Forms\Components\TextInput::make('gtin'),
                Forms\Components\TextInput::make('msrp')->numeric(),
                Forms\Components\TextInput::make('cost')->numeric(),
                Forms\Components\TextInput::make('color'),
                Forms\Components\TextInput::make('size'),
                Forms\Components\TextInput::make('main_image_url')->url()->maxLength(2048),
                Forms\Components\Toggle::make('admin_variant_description_locked')
                    ->label('Lock variant description from feed'),
            ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('item_sku')
            ->columns([
                Tables\Columns\ImageColumn::make('main_image')
                    ->label('Image')
                    ->getStateUsing(fn (ProductVariant $record): ?string => $record->mainImageRelativePath() ?? $record->mainImageUrl())
                    ->disk(fn (?string $state): ?string => $state && ! str_contains($state, '://') ? 'public' : null)
                    ->height(40)
                    ->square(),
                Tables\Columns\TextColumn::make('item_sku')->searchable(),
                Tables\Columns\TextColumn::make('color')->toggleable(),
                Tables\Columns\TextColumn::make('size')->toggleable(),
                Tables\Columns\TextColumn::make('msrp')->numeric()->sortable(),
                Tables\Columns\TextColumn::make('cost')->numeric()->sortable(),
            ])
            ->filters([
                //
            ])
            ->headerActions([
                Tables\Actions\CreateAction::make(),
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ]);
    }
}
