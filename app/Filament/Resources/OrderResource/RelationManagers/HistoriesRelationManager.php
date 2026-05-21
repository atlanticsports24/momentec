<?php

namespace App\Filament\Resources\OrderResource\RelationManagers;

use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Support\Facades\Auth;

class HistoriesRelationManager extends RelationManager
{
    protected static string $relationship = 'histories';

    protected static ?string $title = 'Order history';

    protected static ?string $modelLabel = 'history entry';

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Select::make('order_status_id')
                    ->relationship('orderStatus', 'name')
                    ->required(),
                Forms\Components\Toggle::make('notify'),
                Forms\Components\Textarea::make('comment')->columnSpanFull(),
            ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('orderStatus.name')->label('Status'),
                Tables\Columns\TextColumn::make('comment')->limit(80),
                Tables\Columns\IconColumn::make('notify')->boolean(),
                Tables\Columns\TextColumn::make('user.name')->placeholder('System'),
                Tables\Columns\TextColumn::make('created_at')->dateTime()->sortable(),
            ])
            ->defaultSort('created_at', 'desc')
            ->headerActions([
                Tables\Actions\CreateAction::make()
                    ->label('Add history')
                    ->modalHeading('Add order history')
                    ->mutateFormDataUsing(function (array $data): array {
                        $data['user_id'] = Auth::id();

                        return $data;
                    })
                    ->after(function ($record) {
                        $this->getOwnerRecord()->update([
                            'order_status_id' => $record->order_status_id,
                        ]);
                    }),
            ]);
    }
}
