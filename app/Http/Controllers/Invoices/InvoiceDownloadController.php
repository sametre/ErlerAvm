<?php

namespace App\Http\Controllers\Invoices;

use App\Http\Controllers\Controller;
use App\Models\Order;
use Symfony\Component\HttpFoundation\Response;

class InvoiceDownloadController extends Controller
{
    public function __invoke(string $publicId): Response
    {
        $order = Order::query()
            ->where('user_id', auth()->id())
            ->where('public_id', $publicId)
            ->firstOrFail();

        abort(501, "Invoice download is not connected yet for order {$order->order_number}.");
    }
}
