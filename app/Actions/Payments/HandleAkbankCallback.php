<?php

namespace App\Actions\Payments;

use App\Models\PaymentAttempt;
use App\Services\Akbank\AkbankConfig;
use App\Services\Akbank\AkbankMoney;
use App\Services\Akbank\AkbankPayloadSanitizer;
use App\Services\Akbank\AkbankSignature;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class HandleAkbankCallback
{
    public function __construct(
        private readonly AkbankSignature $signature,
        private readonly AkbankPayloadSanitizer $sanitizer,
    ) {}

    /**
     * @param  array<string, mixed>  $payload
     */
    public function handle(array $payload, string $result): PaymentAttempt
    {
        $config = AkbankConfig::fromConfig();
        $safePayload = $this->sanitizer->redact($payload);

        if (! $this->signature->verifyCallback($config, $payload)) {
            logger()->warning('Akbank callback rejected because hash verification failed.', [
                'merchant_oid' => $this->merchantOid($payload),
                'result' => $result,
                'response_code' => $payload['ProcReturnCode'] ?? null,
            ]);

            throw ValidationException::withMessages(['hash' => 'Akbank imza doğrulaması başarısız.']);
        }

        return DB::transaction(function () use ($payload, $result, $safePayload, $config): PaymentAttempt {
            $attempt = PaymentAttempt::query()
                ->where('provider', 'akbank')
                ->where('merchant_oid', $this->merchantOid($payload))
                ->lockForUpdate()
                ->firstOrFail();

            if ($attempt->isFinalized()) {
                return $attempt;
            }

            $status = $this->isSuccessful($payload, $result, $config) ? 'paid' : 'failed';
            $totalAmount = $this->amountMinor($payload);

            if ($status === 'paid' && $totalAmount !== $attempt->payment_amount) {
                logger()->warning('Akbank callback rejected because paid amount does not match the requested amount.', [
                    'merchant_oid' => $attempt->merchant_oid,
                    'payment_amount' => $attempt->payment_amount,
                    'total_amount' => $totalAmount,
                    'order_id' => $attempt->order_id,
                ]);

                throw ValidationException::withMessages([
                    'amount' => 'Akbank ödeme tutarı sipariş tutarı ile eşleşmiyor.',
                ]);
            }

            if ($status === 'paid' && ! $this->currencyMatches($payload, $attempt->currency, $config)) {
                logger()->warning('Akbank callback rejected because currency does not match the requested currency.', [
                    'merchant_oid' => $attempt->merchant_oid,
                    'order_id' => $attempt->order_id,
                ]);

                throw ValidationException::withMessages([
                    'currency' => 'Akbank ödeme para birimi sipariş para birimi ile eşleşmiyor.',
                ]);
            }

            $attempt->forceFill([
                'status' => $status,
                'total_amount' => $totalAmount,
                'provider_transaction_id' => $payload['TransId'] ?? $payload['transid'] ?? null,
                'authorization_code' => $payload['AuthCode'] ?? null,
                'host_reference' => $payload['HostRefNum'] ?? null,
                'response_code' => $payload['ProcReturnCode'] ?? null,
                'response_message' => $payload['ErrMsg'] ?? $payload['Response'] ?? null,
                'three_d_status' => $payload['mdStatus'] ?? null,
                'masked_card' => $payload['MaskedPan'] ?? $payload['maskedPan'] ?? null,
                'card_brand' => $payload['cardType'] ?? $payload['CARDTYPE'] ?? null,
                'installment_count' => $this->installmentCount($payload),
                'failed_reason_code' => $status === 'failed' ? ($payload['ProcReturnCode'] ?? null) : null,
                'failed_reason_msg' => $status === 'failed' ? ($payload['ErrMsg'] ?? $payload['Response'] ?? 'Akbank ödeme başarısız.') : null,
                'callback_payload' => $safePayload,
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

            logger()->info('Akbank payment attempt finalized.', [
                'merchant_oid' => $attempt->merchant_oid,
                'status' => $attempt->status,
                'payment_amount' => $attempt->payment_amount,
                'total_amount' => $attempt->total_amount,
                'order_id' => $order?->id,
                'response_code' => $attempt->response_code,
            ]);

            return $attempt;
        });
    }

    /**
     * @param  array<string, mixed>  $payload
     */
    private function merchantOid(array $payload): string
    {
        return (string) ($payload['oid'] ?? $payload['ReturnOid'] ?? $payload['merchant_oid'] ?? '');
    }

    /**
     * @param  array<string, mixed>  $payload
     */
    private function isSuccessful(array $payload, string $result, AkbankConfig $config): bool
    {
        $response = strtoupper((string) ($payload['Response'] ?? ''));
        $code = (string) ($payload['ProcReturnCode'] ?? '');
        $threeDStatus = (string) ($payload['mdStatus'] ?? '');
        $threeDStatusAccepted = in_array($threeDStatus, $config->accepted3dStatuses, true);

        return $result === 'success'
            && $code === '00'
            && in_array($response, ['APPROVED', 'SUCCESS'], true)
            && $threeDStatusAccepted;
    }

    /**
     * @param  array<string, mixed>  $payload
     */
    private function currencyMatches(array $payload, string $attemptCurrency, AkbankConfig $config): bool
    {
        $currency = strtoupper((string) ($payload['currency'] ?? $config->currencyCode));

        return in_array($currency, [strtoupper($attemptCurrency), strtoupper($config->currency), $config->currencyCode], true);
    }

    /**
     * @param  array<string, mixed>  $payload
     */
    private function amountMinor(array $payload): int
    {
        $amount = $payload['amount'] ?? $payload['Amount'] ?? $payload['total_amount'] ?? '0';

        return AkbankMoney::toMinor((string) $amount);
    }

    /**
     * @param  array<string, mixed>  $payload
     */
    private function installmentCount(array $payload): ?int
    {
        $installment = $payload['taksit'] ?? $payload['installment'] ?? null;

        return $installment === null || $installment === '' ? null : (int) $installment;
    }
}
