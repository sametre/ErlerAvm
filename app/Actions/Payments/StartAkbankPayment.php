<?php

namespace App\Actions\Payments;

use App\Models\Order;
use App\Models\PaymentAttempt;
use App\Models\User;
use App\Services\Akbank\AkbankConfig;
use App\Services\Akbank\AkbankMoney;
use App\Services\Akbank\AkbankPaymentFormBuilder;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use RuntimeException;

class StartAkbankPayment
{
    public function __construct(
        private readonly AkbankPaymentFormBuilder $formBuilder,
    ) {}

    public function handle(Order $order, Request $request, ?User $user = null): PaymentAttempt
    {
        $config = AkbankConfig::fromConfig();
        $config->assertReady();

        return DB::transaction(function () use ($order, $request, $user): PaymentAttempt {
            $lockedOrder = Order::query()
                ->with('items:id,order_id,name,quantity,unit_price')
                ->whereKey($order->id)
                ->lockForUpdate()
                ->firstOrFail();

            if ($lockedOrder->payment_status === 'paid') {
                throw new RuntimeException('Bu siparişin ödemesi zaten tamamlanmış.');
            }

            if ($lockedOrder->status === 'cancelled') {
                throw new RuntimeException('İptal edilmiş sipariş için ödeme başlatılamaz.');
            }

            if ($lockedOrder->items->isEmpty()) {
                throw new RuntimeException('Sipariş kalemleri bulunamadı.');
            }

            $amountMinor = AkbankMoney::toMinor((string) $lockedOrder->grand_total);
            $merchantOid = 'AKB-'.$lockedOrder->order_number;
            $fields = $this->formBuilder->fields($lockedOrder, $merchantOid, $amountMinor);

            $attempt = PaymentAttempt::query()->firstOrNew([
                'provider' => 'akbank',
                'merchant_oid' => $merchantOid,
            ]);

            if ($attempt->exists && $attempt->status === 'paid') {
                throw new RuntimeException('Bu ödeme denemesi zaten tamamlandı.');
            }

            $attempt->forceFill([
                'idempotency_key' => $attempt->idempotency_key ?: (string) Str::ulid(),
                'user_id' => $user?->id ?? $lockedOrder->user_id,
                'order_id' => $lockedOrder->id,
                'email' => $lockedOrder->email,
                'payment_amount' => $amountMinor,
                'total_amount' => null,
                'currency' => $lockedOrder->currency,
                'status' => 'form_ready',
                'iframe_token' => null,
                'payment_type' => 'card',
                'provider_transaction_id' => null,
                'authorization_code' => null,
                'host_reference' => null,
                'response_code' => null,
                'response_message' => null,
                'three_d_status' => null,
                'masked_card' => null,
                'card_brand' => null,
                'installment_count' => null,
                'failed_reason_code' => null,
                'failed_reason_msg' => null,
                'request_payload' => $fields,
                'provider_response' => [
                    'ip_address' => $request->ip(),
                    'user_agent' => Str::limit((string) $request->userAgent(), 500, ''),
                ],
                'callback_payload' => null,
                'notified_at' => null,
                'redirected_at' => now(),
                'completed_at' => null,
            ])->save();

            logger()->info('Akbank payment form prepared.', [
                'payment_attempt_id' => $attempt->id,
                'merchant_oid' => $attempt->merchant_oid,
                'order_id' => $lockedOrder->id,
                'amount_minor' => $amountMinor,
            ]);

            return $attempt;
        });
    }
}
