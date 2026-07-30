<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Traits\ApiResponse;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class OrderController extends Controller
{
    use ApiResponse;

    public function index(Request $request): JsonResponse
    {
        $orders = $request->user()->orders()
            ->latest()
            ->paginate(10);

        $orders->getCollection()->transform(function ($order) {
            return [
                'public_id' => $order->public_id,
                'order_number' => $order->order_number,
                'status' => $order->status,
                'status_label' => $order->status_label,
                'payment_status' => $order->payment_status,
                'payment_status_label' => $order->payment_status_label,
                'grand_total' => $order->grand_total,
                'currency' => $order->currency,
                'payment_required' => $order->payment_status !== 'paid',
                'created_at' => $order->created_at->format('d.m.Y H:i'),
            ];
        });

        return $this->paginated($orders);
    }

    public function show(Request $request, string $publicId): JsonResponse
    {
        $order = $request->user()->orders()
            ->with(['items.product:id,slug,name,public_id'])
            ->where('public_id', $publicId)
            ->firstOrFail();

        return $this->success([
            'public_id' => $order->public_id,
            'order_number' => $order->order_number,
            'status' => $order->status,
            'status_label' => $order->status_label,
            'payment_status' => $order->payment_status,
            'payment_status_label' => $order->payment_status_label,
            'subtotal' => $order->subtotal,
            'shipping_total' => $order->shipping_total,
            'discount_total' => $order->discount_total,
            'grand_total' => $order->grand_total,
            'currency' => $order->currency,
            'payment_required' => $order->payment_status !== 'paid',
            'payment_endpoint' => route('api.v1.orders.payment.store', ['public_id' => $order->public_id], false),
            'shipping_address' => $order->shipping_address,
            'billing_address' => $order->billing_address,
            'created_at' => $order->created_at->format('d.m.Y H:i'),
            'items' => $order->items->map(fn ($item) => [
                'public_id' => $item->public_id,
                'name' => $item->name,
                'sku' => $item->sku,
                'quantity' => $item->quantity,
                'unit_price' => $item->unit_price,
                'line_total' => $item->line_total,
                'snapshot' => $item->snapshot,
                'product_public_id' => $item->product_public_id,
                'product_url' => $item->product ? route('products.show', ['product' => $item->product->slug.'-'.$item->product->public_id]) : null,
            ]),
        ]);
    }
}
