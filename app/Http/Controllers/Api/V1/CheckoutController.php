<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Traits\ApiResponse;
use App\Services\Api\ApiCheckoutService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use RuntimeException;

class CheckoutController extends Controller
{
    use ApiResponse;

    public function __construct(private readonly ApiCheckoutService $checkout) {}

    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'shipping_full_name' => ['required', 'string', 'max:255'],
            'shipping_phone' => ['required', 'string', 'max:20'],
            'shipping_city' => ['required', 'string', 'max:100'],
            'shipping_district' => ['required', 'string', 'max:100'],
            'shipping_postal_code' => ['nullable', 'string', 'max:20'],
            'shipping_address_line' => ['required', 'string', 'max:1000'],

            'billing_same_as_shipping' => ['required', 'boolean'],
            'billing_type' => ['required', Rule::in(['individual', 'corporate'])],

            'billing_full_name' => ['required_if:billing_same_as_shipping,false', 'nullable', 'string', 'max:255'],
            'billing_identity_number' => ['required_if:billing_type,individual', 'nullable', 'string', 'size:11'],
            'billing_company_name' => ['required_if:billing_type,corporate', 'nullable', 'string', 'max:255'],
            'billing_tax_number' => ['required_if:billing_type,corporate', 'nullable', 'string', 'max:50'],
            'billing_tax_office' => ['required_if:billing_type,corporate', 'nullable', 'string', 'max:100'],
            'billing_city' => ['required_if:billing_same_as_shipping,false', 'nullable', 'string', 'max:100'],
            'billing_district' => ['required_if:billing_same_as_shipping,false', 'nullable', 'string', 'max:100'],
            'billing_postal_code' => ['nullable', 'string', 'max:20'],
            'billing_address_line' => ['required_if:billing_same_as_shipping,false', 'nullable', 'string', 'max:1000'],

            'notes' => ['nullable', 'string', 'max:1000'],
        ]);

        try {
            $order = $this->checkout->createOrder($request->user(), $validated);

            return $this->success([
                'order' => [
                    'public_id' => $order->public_id,
                    'order_number' => $order->order_number,
                    'status' => $order->status,
                    'status_label' => $order->status_label,
                    'payment_status' => $order->payment_status,
                    'payment_status_label' => $order->payment_status_label,
                    'grand_total' => $order->grand_total,
                    'currency' => $order->currency,
                    'payment_required' => $order->payment_status !== 'paid',
                    'payment_endpoint' => route('api.v1.orders.payment.store', ['public_id' => $order->public_id], false),
                ],
            ], 'Siparişiniz başarıyla oluşturuldu.', 201);
        } catch (RuntimeException $e) {
            return $this->error($e->getMessage(), 400);
        }
    }
}
