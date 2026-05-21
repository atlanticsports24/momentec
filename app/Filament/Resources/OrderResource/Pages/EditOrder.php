<?php

namespace App\Filament\Resources\OrderResource\Pages;

use App\Filament\Resources\OrderResource;
use App\Services\Store\OrderService;
use Filament\Actions;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\EditRecord;
use Illuminate\Support\Facades\Auth;

class EditOrder extends EditRecord
{
    protected static string $resource = OrderResource::class;

    protected static ?string $title = 'Update order status';

    protected function getHeaderActions(): array
    {
        return [
            Actions\ViewAction::make(),
        ];
    }

    protected function mutateFormDataBeforeFill(array $data): array
    {
        return [
            'order_status_id' => $this->record->order_status_id,
            'status_comment' => '',
            'notify_customer' => false,
        ];
    }

    protected function getFormActions(): array
    {
        return [
            Actions\Action::make('save')
                ->label('Update status')
                ->submit('save'),
            Actions\Action::make('cancel')
                ->label('Cancel')
                ->url(fn () => OrderResource::getUrl('view', ['record' => $this->record]))
                ->color('gray'),
        ];
    }

    public function save(bool $shouldRedirect = true, bool $shouldSendSavedNotification = true): void
    {
        $this->authorizeAccess();

        $this->form->validate();

        $data = $this->form->getState();

        app(OrderService::class)->updateStatus(
            $this->getRecord(),
            (int) $data['order_status_id'],
            filled($data['status_comment'] ?? null) ? $data['status_comment'] : null,
            (bool) ($data['notify_customer'] ?? false),
            Auth::id()
        );

        $this->record->refresh();

        if ($shouldSendSavedNotification) {
            Notification::make()
                ->success()
                ->title('Order status updated')
                ->body('The order status and history entry have been saved.')
                ->send();
        }

        if ($shouldRedirect) {
            $this->redirect(OrderResource::getUrl('view', ['record' => $this->record]));
        }
    }
}
