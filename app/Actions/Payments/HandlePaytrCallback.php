<?php

namespace App\Actions\Payments;

use App\Models\PaymentAttempt;
use App\Services\Paytr\PaytrConfig;
use App\Services\Paytr\PaytrHash;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class HandlePaytrCallback
{
    public function __construct(private readonly PaytrHash $hash) {}

    /**
     * @param  array<string, mixed>  $payload
     */
    public function handle(array $payload): PaymentAttempt
    {
        $config = PaytrConfig::fromConfig();

        if (! $this->hash->verifyCallback($config, $payload)) {
            logger()->warning('PayTR callback rejected because hash verification failed.', [
                'merchant_oid' => $payload['merchant_oid'] ?? null,
                'status' => $payload['status'] ?? null,
            ]);

            throw ValidationException::withMessages(['hash' => 'PayTR hash doğrulaması başarısız.']);
        }

        return DB::transaction(function () use ($payload) {
            $attempt = PaymentAttempt::query()
                ->where('provider', 'paytr')
                ->where('merchant_oid', $payload['merchant_oid'])
                ->lockForUpdate()
                ->firstOrFail();

            if ($attempt->isFinalized()) {
                return $attempt;
            }

            $status = $payload['status'] === 'success' ? 'paid' : 'failed';
            $totalAmount = (int) $payload['total_amount'];

            if ($status === 'paid' && $totalAmount !== $attempt->payment_amount) {
                logger()->warning('PayTR callback rejected because paid amount does not match the requested amount.', [
                    'merchant_oid' => $attempt->merchant_oid,
                    'payment_amount' => $attempt->payment_amount,
                    'total_amount' => $totalAmount,
                    'order_id' => $attempt->order_id,
                ]);

                throw ValidationException::withMessages([
                    'total_amount' => 'PayTR ödeme tutarı sipariş tutarı ile eşleşmiyor.',
                ]);
            }

            $attempt->forceFill([
                'status' => $status,
                'total_amount' => $totalAmount,
                'payment_type' => $payload['payment_type'] ?? null,
                'installment_count' => isset($payload['installment_count']) ? (int) $payload['installment_count'] : null,
                'failed_reason_code' => $payload['failed_reason_code'] ?? null,
                'failed_reason_msg' => $payload['failed_reason_msg'] ?? null,
                'callback_payload' => $payload,
                'notified_at' => now(),
                'completed_at' => now(),
            ])->save();

            $order = $attempt->order()->lockForUpdate()->first();

            if ($order !== null) {
                if ($status === 'paid') {
                    $order->forceFill([
                        'payment_status' => 'paid',
                        'status' => $order->status === 'payment_pending' ? 'processing' : $order->status,
                    ])->save();
                } elseif ($order->payment_status !== 'paid') {
                    $order->forceFill([
                        'payment_status' => 'failed',
                    ])->save();
                }
            }

            logger()->info('PayTR payment attempt finalized.', [
                'merchant_oid' => $attempt->merchant_oid,
                'status' => $attempt->status,
                'payment_amount' => $attempt->payment_amount,
                'total_amount' => $attempt->total_amount,
                'order_id' => $order?->id,
            ]);

            return $attempt;
        });
    }
}
