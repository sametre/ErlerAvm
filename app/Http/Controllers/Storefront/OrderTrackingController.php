<?php

namespace App\Http\Controllers\Storefront;

use App\Http\Controllers\Controller;
use App\Models\Order;
use Inertia\Inertia;
use Inertia\Response;

class OrderTrackingController extends Controller
{
    public function show(string $publicId): Response
    {
        $order = Order::query()
            ->where('public_id', $publicId)
            ->firstOrFail();

        return Inertia::render('Storefront/OrderTracking', [
            'order' => $order->only(['public_id', 'order_number', 'status', 'payment_status', 'grand_total', 'currency', 'created_at']),
        ]);
    }
}
