<?php

namespace App\Filament\Resources;

use App\Filament\Resources\OrderResource\Pages;
use App\Filament\Resources\OrderResource\RelationManagers;
use App\Models\Order;
use App\Models\OrderStatus;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Infolists;
use Filament\Infolists\Infolist;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class OrderResource extends Resource
{
    protected static ?string $model = Order::class;

    protected static ?string $navigationIcon = 'heroicon-o-shopping-bag';

    protected static ?string $navigationGroup = 'Store';

    protected static ?int $navigationSort = 10;

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('Order')
                    ->schema([
                        Forms\Components\Placeholder::make('order_number')
                            ->label('Order number')
                            ->content(fn (?Order $record): string => $record?->order_number ?? '—'),
                        Forms\Components\Placeholder::make('current_status')
                            ->label('Current status')
                            ->content(fn (?Order $record): string => $record?->status?->name ?? '—'),
                        Forms\Components\Placeholder::make('customer')
                            ->label('Customer')
                            ->content(fn (?Order $record): string => trim(($record?->customer_firstname ?? '').' '.($record?->customer_lastname ?? '')) ?: ($record?->customer_email ?? '—')),
                    ])
                    ->columns(3),
                Forms\Components\Section::make('Update order status')
                    ->description('Select a new status and optionally add a comment. This is saved to the order history (like OpenCart).')
                    ->schema([
                        Forms\Components\Select::make('order_status_id')
                            ->label('Order status')
                            ->options(fn () => OrderStatus::query()->orderBy('sort_order')->pluck('name', 'id'))
                            ->required()
                            ->native(false)
                            ->searchable(),
                        Forms\Components\Textarea::make('status_comment')
                            ->label('Comment')
                            ->placeholder('e.g. Shipped via UPS, tracking #1Z999...')
                            ->rows(4)
                            ->columnSpanFull(),
                        Forms\Components\Toggle::make('notify_customer')
                            ->label('Notify customer')
                            ->default(false),
                    ]),
            ]);
    }

    public static function infolist(Infolist $infolist): Infolist
    {
        return $infolist
            ->schema([
                Infolists\Components\Section::make('Order')
                    ->columns(3)
                    ->schema([
                        Infolists\Components\TextEntry::make('order_number'),
                        Infolists\Components\TextEntry::make('status.name')->badge(),
                        Infolists\Components\TextEntry::make('total')->money('USD'),
                        Infolists\Components\TextEntry::make('created_at')->dateTime(),
                        Infolists\Components\TextEntry::make('paid_at')->dateTime()->placeholder('—'),
                    ]),
                Infolists\Components\Section::make('Customer')
                    ->columns(2)
                    ->schema([
                        Infolists\Components\TextEntry::make('customer_email'),
                        Infolists\Components\TextEntry::make('customer_telephone'),
                        Infolists\Components\TextEntry::make('customer_firstname'),
                        Infolists\Components\TextEntry::make('customer_lastname'),
                    ]),
                Infolists\Components\Section::make('Payment & shipping')
                    ->columns(2)
                    ->schema([
                        Infolists\Components\TextEntry::make('payment_method_name'),
                        Infolists\Components\TextEntry::make('shipping_method_name'),
                        Infolists\Components\TextEntry::make('subtotal')->money('USD'),
                        Infolists\Components\TextEntry::make('shipping_total')->money('USD'),
                    ]),
                Infolists\Components\Section::make('Shipping address')
                    ->schema([
                        Infolists\Components\TextEntry::make('shipping_address_1'),
                        Infolists\Components\TextEntry::make('shipping_city'),
                        Infolists\Components\TextEntry::make('shipping_postcode'),
                        Infolists\Components\TextEntry::make('shippingCountry.name')->label('Country'),
                    ]),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('order_number')->searchable()->sortable(),
                Tables\Columns\TextColumn::make('status.name')->badge()->sortable(),
                Tables\Columns\TextColumn::make('customer_email')->searchable(),
                Tables\Columns\TextColumn::make('payment_method_name')->label('Payment'),
                Tables\Columns\TextColumn::make('total')->money('USD')->sortable(),
                Tables\Columns\TextColumn::make('created_at')->dateTime()->sortable(),
            ])
            ->defaultSort('created_at', 'desc')
            ->filters([
                Tables\Filters\SelectFilter::make('order_status_id')
                    ->label('Status')
                    ->options(fn () => OrderStatus::query()->orderBy('sort_order')->pluck('name', 'id')),
            ])
            ->actions([
                Tables\Actions\ViewAction::make(),
                Tables\Actions\EditAction::make()
                    ->label('Update status'),
            ]);
    }

    public static function getRelations(): array
    {
        return [
            RelationManagers\ProductsRelationManager::class,
            RelationManagers\HistoriesRelationManager::class,
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListOrders::route('/'),
            'view' => Pages\ViewOrder::route('/{record}'),
            'edit' => Pages\EditOrder::route('/{record}/edit'),
        ];
    }

    public static function canCreate(): bool
    {
        return false;
    }
}
