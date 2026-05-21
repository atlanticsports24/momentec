<?php

namespace App\Services\Store;

use App\Models\Currency;
use App\Models\Order;
use App\Models\OrderHistory;
use App\Models\OrderProduct;
use App\Models\OrderStatus;
use App\Models\OrderTotal;
use App\Models\PaymentMethod;
use App\Models\ShippingMethod;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class OrderService
{
    public function __construct(
        private readonly StoreSettings $settings,
        private readonly CartService $cart,
    ) {}

    public function createFromCheckout(array $data): Order
    {
        return DB::transaction(function () use ($data) {
            $lines = $this->cart->lines();
            $subtotal = $this->cart->subtotal();

            $shippingMethod = ShippingMethod::query()->findOrFail($data['shipping_method_id']);
            $paymentMethod = PaymentMethod::query()->findOrFail($data['payment_method_id']);

            $shippingTotal = $shippingMethod->calculateCost($subtotal);
            $taxTotal = 0.0;
            $total = $subtotal + $shippingTotal + $taxTotal;

            $defaultStatusId = (int) $this->settings->get('default_order_status_id');
            $status = OrderStatus::query()->find($defaultStatusId)
                ?? OrderStatus::query()->where('code', 'missing')->firstOrFail();

            $currency = Currency::query()->where('is_default', true)->first()
                ?? Currency::query()->where('code', 'USD')->first();

            $order = Order::query()->create([
                'order_number' => $this->generateOrderNumber(),
                'order_status_id' => $status->id,
                'payment_method_id' => $paymentMethod->id,
                'shipping_method_id' => $shippingMethod->id,
                'currency_id' => $currency?->id,
                'currency_code' => $currency?->code ?? 'USD',
                'currency_value' => $currency?->value ?? 1,
                'customer_email' => $data['customer_email'] ?? null,
                'customer_firstname' => $data['customer_firstname'] ?? null,
                'customer_lastname' => $data['customer_lastname'] ?? null,
                'customer_telephone' => $data['customer_telephone'] ?? null,
                'payment_firstname' => $data['payment_firstname'] ?? $data['customer_firstname'] ?? null,
                'payment_lastname' => $data['payment_lastname'] ?? $data['customer_lastname'] ?? null,
                'payment_address_1' => $data['payment_address_1'] ?? null,
                'payment_address_2' => $data['payment_address_2'] ?? null,
                'payment_city' => $data['payment_city'] ?? null,
                'payment_postcode' => $data['payment_postcode'] ?? null,
                'payment_country_id' => $data['payment_country_id'] ?? null,
                'payment_zone_id' => $data['payment_zone_id'] ?? null,
                'shipping_firstname' => $data['shipping_firstname'] ?? $data['customer_firstname'] ?? null,
                'shipping_lastname' => $data['shipping_lastname'] ?? $data['customer_lastname'] ?? null,
                'shipping_address_1' => $data['shipping_address_1'] ?? $data['payment_address_1'] ?? null,
                'shipping_address_2' => $data['shipping_address_2'] ?? $data['payment_address_2'] ?? null,
                'shipping_city' => $data['shipping_city'] ?? $data['payment_city'] ?? null,
                'shipping_postcode' => $data['shipping_postcode'] ?? $data['payment_postcode'] ?? null,
                'shipping_country_id' => $data['shipping_country_id'] ?? $data['payment_country_id'] ?? null,
                'shipping_zone_id' => $data['shipping_zone_id'] ?? $data['payment_zone_id'] ?? null,
                'payment_method_code' => $paymentMethod->code,
                'payment_method_name' => $paymentMethod->name,
                'shipping_method_code' => $shippingMethod->code,
                'shipping_method_name' => $shippingMethod->name,
                'comment' => $data['comment'] ?? null,
                'subtotal' => $subtotal,
                'shipping_total' => $shippingTotal,
                'tax_total' => $taxTotal,
                'total' => $total,
                'ip_address' => request()->ip(),
                'user_agent' => (string) request()->userAgent(),
            ]);

            foreach ($lines as $line) {
                OrderProduct::query()->create([
                    'order_id' => $order->id,
                    'product_id' => $line['product']?->id,
                    'product_variant_id' => $line['variant']->id,
                    'name' => $line['product']?->name ?? $line['variant']->item_sku,
                    'item_sku' => $line['variant']->item_sku,
                    'quantity' => $line['quantity'],
                    'price' => $line['price'],
                    'total' => $line['total'],
                    'options' => [
                        'color' => $line['variant']->color,
                        'size' => $line['variant']->size,
                    ],
                ]);
            }

            $this->addTotal($order, 'sub_total', 'Sub-Total', $subtotal, 1);
            $this->addTotal($order, 'shipping', $shippingMethod->name, $shippingTotal, 2);
            if ($taxTotal > 0) {
                $this->addTotal($order, 'tax', 'Tax', $taxTotal, 3);
            }
            $this->addTotal($order, 'total', 'Total', $total, 9);

            $this->recordHistory($order, $status->id, 'Order created');

            if ($this->shouldMarkPaidImmediately($paymentMethod)) {
                $this->markPaymentSuccess($order, $paymentMethod);
            }

            $this->cart->clear();

            return $order->fresh(['products', 'totals', 'status']);
        });
    }

    public function markPaymentSuccess(Order $order, ?PaymentMethod $paymentMethod = null): void
    {
        $paymentMethod ??= $order->paymentMethod;

        $successStatusId = $paymentMethod?->success_order_status_id
            ?? $this->settings->get('default_order_status_id');

        if (! $successStatusId) {
            return;
        }

        $order->update([
            'order_status_id' => $successStatusId,
            'paid_at' => now(),
        ]);

        $this->recordHistory($order, (int) $successStatusId, 'Payment successful');
    }

    public function updateStatus(Order $order, int $statusId, ?string $comment = null, bool $notify = false, ?int $userId = null): void
    {
        $order->update(['order_status_id' => $statusId]);

        $this->recordHistory($order, $statusId, $comment, $notify, $userId);
    }

    private function recordHistory(Order $order, int $statusId, ?string $comment = null, bool $notify = false, ?int $userId = null): void
    {
        OrderHistory::query()->create([
            'order_id' => $order->id,
            'order_status_id' => $statusId,
            'notify' => $notify,
            'comment' => $comment,
            'user_id' => $userId,
        ]);
    }

    private function addTotal(Order $order, string $code, string $title, float $value, int $sort): void
    {
        OrderTotal::query()->create([
            'order_id' => $order->id,
            'code' => $code,
            'title' => $title,
            'value' => $value,
            'sort_order' => $sort,
        ]);
    }

    private function shouldMarkPaidImmediately(PaymentMethod $method): bool
    {
        return in_array($method->code, ['cod', 'free_checkout'], true);
    }

    private function generateOrderNumber(): string
    {
        do {
            $number = 'M'.now()->format('ymd').strtoupper(Str::random(6));
        } while (Order::query()->where('order_number', $number)->exists());

        return $number;
    }
}
