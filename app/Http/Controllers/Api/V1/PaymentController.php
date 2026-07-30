<?php

namespace App\Http\Controllers\Api\V1;

use App\Actions\Payments\StartAkbankPayment;
use App\Http\Controllers\Controller;
use App\Http\Traits\ApiResponse;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use RuntimeException;

class PaymentController extends Controller
{
    use ApiResponse;

    public function store(Request $request, string $publicId, StartAkbankPayment $payment): JsonResponse
    {
        $order = $request->user()
            ->orders()
            ->with('items:id,order_id,name,quantity,unit_price')
            ->where('public_id', $publicId)
            ->firstOrFail();

        if ($order->payment_status === 'paid') {
            return $this->success([
                'payment_required' => false,
                'payment_status' => $order->payment_status,
                'checkout_url' => null,
            ], 'Bu siparişin ödemesi zaten tamamlanmış.');
        }

        if ($order->status === 'cancelled') {
            return $this->error('İptal edilmiş sipariş için ödeme başlatılamaz.', 409);
        }

        if ($order->items->isEmpty()) {
            return $this->error('Sipariş kalemleri bulunamadı.', 422);
        }

        try {
            $attempt = $payment->handle($order, $request, $request->user());
        } catch (RuntimeException $exception) {
            logger()->warning('Mobile API Akbank payment start failed.', [
                'order_id' => $order->id,
                'order_number' => $order->order_number,
                'user_id' => $request->user()->id,
                'reason' => $exception->getMessage(),
            ]);

            return $this->error($exception->getMessage(), 422);
        }

        return $this->success([
            'payment_required' => true,
            'payment_status' => $order->payment_status,
            'provider' => 'akbank',
            'checkout_url' => route('payments.akbank.attempt', ['paymentAttempt' => $attempt->idempotency_key]),
        ], 'Ödeme bağlantısı hazırlandı.');
    }
}
