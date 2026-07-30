<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Order;
use Illuminate\Http\JsonResponse;

class OrderNotificationController extends Controller
{
    public function __invoke(): JsonResponse
    {
        $latest = Order::query()
            ->with(['items:id,order_id,name,sku,quantity,line_total'])
            ->latest()
            ->first();

        return response()->json([
            'count' => Order::query()->count(),
            'latest_order_id' => $latest?->public_id,
            'latest_order_number' => $latest?->order_number,
            'latest_created_at' => $latest?->created_at?->toIso8601String(),
            'latest_order' => $latest ? [
                'public_id' => $latest->public_id,
                'order_number' => $latest->order_number,
                'email' => $latest->email,
                'status' => $latest->status,
                'status_label' => $latest->status_label,
                'payment_status' => $latest->payment_status,
                'payment_status_label' => $latest->payment_status_label,
                'grand_total' => number_format((float) $latest->grand_total, 2, ',', '.').' '.$latest->currency,
                'shipping_address' => $latest->shipping_address,
                'created_at' => $latest->created_at?->toDateTimeString(),
                'items' => $latest->items->map(fn ($item) => [
                    'name' => $item->name,
                    'sku' => $item->sku,
                    'quantity' => $item->quantity,
                    'line_total' => number_format((float) $item->line_total, 2, ',', '.').' '.$latest->currency,
                ])->values(),
                'url' => route('admin.orders.show', ['order' => $latest->public_id], false),
            ] : null,
        ]);
    }
}
