<?php

namespace App\Filament\Resources\OrderResource\Pages;

use App\Filament\Resources\OrderResource;
use App\Models\OrderStatus;
use App\Services\Store\OrderService;
use Filament\Actions;
use Filament\Forms;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\ViewRecord;
use Illuminate\Support\Facades\Auth;

class ViewOrder extends ViewRecord
{
    protected static string $resource = OrderResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\EditAction::make()
                ->label('Update status'),
            Actions\Action::make('updateStatus')
                ->label('Quick update status')
                ->icon('heroicon-o-arrow-path')
                ->form([
                    Forms\Components\Select::make('order_status_id')
                        ->label('Order status')
                        ->options(fn () => OrderStatus::query()->orderBy('sort_order')->pluck('name', 'id'))
                        ->default(fn () => $this->record->order_status_id)
                        ->required()
                        ->native(false)
                        ->searchable(),
                    Forms\Components\Textarea::make('status_comment')
                        ->label('Comment')
                        ->rows(4),
                    Forms\Components\Toggle::make('notify_customer')
                        ->label('Notify customer'),
                ])
                ->action(function (array $data): void {
                    app(OrderService::class)->updateStatus(
                        $this->record,
                        (int) $data['order_status_id'],
                        filled($data['status_comment'] ?? null) ? $data['status_comment'] : null,
                        (bool) ($data['notify_customer'] ?? false),
                        Auth::id()
                    );

                    $this->record->refresh();

                    Notification::make()
                        ->success()
                        ->title('Order status updated')
                        ->send();
                }),
        ];
    }
}
