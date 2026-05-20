<?php

namespace App\Filament\Resources\ProductResource\RelationManagers;

use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables;
use Filament\Tables\Table;

class VariantDisplayOptionsRelationManager extends RelationManager
{
    protected static string $relationship = 'variantDisplayOptions';

    public function form(Form $form): Form
    {
        return $form
            ->schema([
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
                Forms\Components\TextInput::make('sort_order')->numeric()->default(0),
                Forms\Components\TextInput::make('label')->maxLength(255),
            ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('dimension')
            ->columns([
                Tables\Columns\TextColumn::make('dimension'),
                Tables\Columns\TextColumn::make('display_type'),
                Tables\Columns\TextColumn::make('sort_order')->sortable(),
                Tables\Columns\TextColumn::make('label'),
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
