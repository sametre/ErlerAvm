<?php

namespace App\Services\Api;

use App\Models\Order;
use App\Models\Product;
use App\Models\User;
use App\Services\Notifications\UserNotificationService;
use Illuminate\Support\Facades\DB;
use RuntimeException;

class ApiCheckoutService
{
    public function __construct(
        private readonly ApiCartService $cart,
        private readonly UserNotificationService $notifications,
    ) {}

    /**
     * @param  array<string, mixed>  $data
     */
    public function createOrder(User $user, array $data): Order
    {
        $quantities = $this->cart->rawQuantities($user);

        if ($quantities === []) {
            throw new RuntimeException('Sepetiniz boş.');
        }

        return DB::transaction(function () use ($user, $data, $quantities): Order {
            $products = Product::query()
                ->whereIn('id', array_keys($quantities))
                ->where('status', 'published')
                ->whereNotNull('published_at')
                ->lockForUpdate()
                ->get();

            if ($products->count() !== count($quantities)) {
                throw new RuntimeException('Sepette yayından kalkmış ürün var.');
            }

            $subtotal = 0.0;

            foreach ($products as $product) {
                $quantity = $quantities[$product->id] ?? 0;

                if ($quantity <= 0 || $product->stock_quantity < $quantity) {
                    throw new RuntimeException($product->name.' için yeterli stok yok.');
                }

                $subtotal += (float) $product->price * $quantity;
            }

            $shippingTotal = $this->shippingTotal($subtotal);
            $taxTotal = 0.0;
            $discountTotal = 0.0;
            $grandTotal = $subtotal + $shippingTotal + $taxTotal - $discountTotal;

            $order = Order::query()->create([
                'order_number' => $this->orderNumber(),
                'user_id' => $user->id,
                'email' => $user->email,
                'status' => 'payment_pending',
                'payment_status' => 'pending',
                'subtotal' => number_format($subtotal, 2, '.', ''),
                'shipping_total' => number_format($shippingTotal, 2, '.', ''),
                'discount_total' => number_format($discountTotal, 2, '.', ''),
                'tax_total' => number_format($taxTotal, 2, '.', ''),
                'grand_total' => number_format($grandTotal, 2, '.', ''),
                'currency' => 'TRY',
                'shipping_address' => $this->shippingAddress($data),
                'billing_address' => $this->billingAddress($data),
            ]);

            foreach ($products as $product) {
                $quantity = $quantities[$product->id];
                $lineTotal = (float) $product->price * $quantity;

                $order->items()->create([
                    'product_id' => $product->id,
                    'product_public_id' => $product->public_id,
                    'name' => $product->name,
                    'sku' => $product->sku,
                    'quantity' => $quantity,
                    'unit_price' => $product->price,
                    'line_total' => number_format($lineTotal, 2, '.', ''),
                    'snapshot' => [
                        'slug' => $product->slug,
                        'images' => $product->images ?? [],
                        'price' => $product->price,
                    ],
                ]);

                $product->decrement('stock_quantity', $quantity);
            }

            $this->cart->clear($user);

            logger()->info('API Checkout order created.', [
                'order_id' => $order->id,
                'order_number' => $order->order_number,
                'user_id' => $user->id,
            ]);

            $this->notifications->createForUser(
                user: $user,
                type: 'order.created',
                title: 'Siparişiniz alındı',
                body: $order->order_number.' numaralı siparişiniz oluşturuldu. Ödeme durumunu hesabınızdan takip edebilirsiniz.',
                actionUrl: '/hesabim/siparislerim/'.$order->public_id,
                data: [
                    'order_public_id' => $order->public_id,
                    'order_number' => $order->order_number,
                    'grand_total' => $order->grand_total,
                ],
            );

            return $order;
        });
    }

    private function shippingTotal(float $subtotal): float
    {
        return $subtotal >= 5000 ? 0.0 : 249.90;
    }

    /**
     * @param  array<string, mixed>  $data
     * @return array<string, mixed>
     */
    private function shippingAddress(array $data): array
    {
        return [
            'full_name' => $data['shipping_full_name'],
            'phone' => $data['shipping_phone'],
            'city' => $data['shipping_city'],
            'district' => $data['shipping_district'],
            'postal_code' => $data['shipping_postal_code'] ?? null,
            'address_line' => $data['shipping_address_line'],
        ];
    }

    /**
     * @param  array<string, mixed>  $data
     * @return array<string, mixed>
     */
    private function billingAddress(array $data): array
    {
        $sameAsShipping = (bool) ($data['billing_same_as_shipping'] ?? true);

        return [
            'type' => $data['billing_type'],
            'same_as_shipping' => $sameAsShipping,
            'full_name' => $data['billing_full_name'] ?? $data['shipping_full_name'],
            'identity_number' => $data['billing_identity_number'] ?? null,
            'company_name' => $data['billing_company_name'] ?? null,
            'tax_number' => $data['billing_tax_number'] ?? null,
            'tax_office' => $data['billing_tax_office'] ?? null,
            'city' => $sameAsShipping ? $data['shipping_city'] : ($data['billing_city'] ?? null),
            'district' => $sameAsShipping ? $data['shipping_district'] : ($data['billing_district'] ?? null),
            'postal_code' => $sameAsShipping ? ($data['shipping_postal_code'] ?? null) : ($data['billing_postal_code'] ?? null),
            'address_line' => $sameAsShipping ? $data['shipping_address_line'] : ($data['billing_address_line'] ?? null),
            'notes' => $data['notes'] ?? null,
        ];
    }

    private function orderNumber(): string
    {
        do {
            $number = 'ERL-API-'.now()->format('Ymd').'-'.str_pad((string) random_int(1, 999999), 6, '0', STR_PAD_LEFT);
        } while (Order::query()->where('order_number', $number)->exists());

        return $number;
    }
}
