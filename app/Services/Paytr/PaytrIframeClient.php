<?php

namespace App\Services\Paytr;

use App\Models\PaymentAttempt;
use Illuminate\Http\Client\ConnectionException;
use Illuminate\Support\Facades\Http;
use RuntimeException;

class PaytrIframeClient
{
    public function __construct(private readonly PaytrHash $hash) {}

    /**
     * @param  array<int, array{0: string, 1: string, 2: int}>  $basket
     */
    public function createToken(
        string $merchantOid,
        string $userIp,
        string $email,
        int $paymentAmount,
        array $basket,
        string $userName,
        string $userAddress,
        string $userPhone,
        ?int $userId = null,
        ?int $orderId = null,
    ): PaymentAttempt {
        $config = PaytrConfig::fromConfig();
        $userBasket = base64_encode(json_encode($basket, JSON_UNESCAPED_UNICODE | JSON_THROW_ON_ERROR));

        $payload = [
            'merchant_id' => $config->merchantId,
            'user_ip' => $userIp,
            'merchant_oid' => $merchantOid,
            'email' => $email,
            'payment_amount' => (string) $paymentAmount,
            'user_basket' => $userBasket,
            'debug_on' => $config->debugOn ? '1' : '0',
            'no_installment' => (string) $config->noInstallment,
            'max_installment' => (string) $config->maxInstallment,
            'user_name' => $userName,
            'user_address' => $userAddress,
            'user_phone' => $userPhone,
            'merchant_ok_url' => $config->okUrl,
            'merchant_fail_url' => $config->failUrl,
            'timeout_limit' => (string) $config->timeoutLimit,
            'currency' => $config->currency,
            'test_mode' => $config->testMode ? '1' : '0',
            'lang' => $config->lang,
        ];

        $payload['paytr_token'] = $this->hash->iframeToken($config, $payload);

        $attempt = PaymentAttempt::query()->firstOrNew([
            'provider' => 'paytr',
            'merchant_oid' => $merchantOid,
        ]);

        if ($attempt->exists && $attempt->status === 'paid') {
            throw new RuntimeException('Bu ödeme denemesi zaten tamamlandı.');
        }

        $attempt->forceFill([
            'user_id' => $userId,
            'order_id' => $orderId,
            'email' => $email,
            'payment_amount' => $paymentAmount,
            'currency' => $config->currency,
            'status' => 'token_requested',
            'iframe_token' => null,
            'total_amount' => null,
            'payment_type' => null,
            'installment_count' => null,
            'failed_reason_code' => null,
            'failed_reason_msg' => null,
            'request_payload' => $this->redactPayload($payload),
            'provider_response' => null,
            'callback_payload' => null,
            'notified_at' => null,
            'completed_at' => null,
        ])->save();

        try {
            $response = Http::asForm()
                ->timeout(20)
                ->connectTimeout(10)
                ->post($config->iframeTokenUrl, $payload);
        } catch (ConnectionException $exception) {
            $attempt->forceFill(['status' => 'token_failed'])->save();

            report($exception);

            throw new RuntimeException('PayTR token servisine bağlanılamadı.');
        }

        $data = $response->json();

        if (! $response->ok() || ! is_array($data) || ($data['status'] ?? null) !== 'success') {
            $attempt->forceFill([
                'status' => 'token_failed',
                'provider_response' => is_array($data) ? $data : ['body' => $response->body()],
            ])->save();

            logger()->warning('PayTR iframe token request failed.', [
                'merchant_oid' => $merchantOid,
                'http_status' => $response->status(),
                'response' => $data ?? $response->body(),
            ]);

            throw new RuntimeException((string) ($data['reason'] ?? 'PayTR ödeme tokeni alınamadı.'));
        }

        $attempt->forceFill([
            'status' => 'pending',
            'iframe_token' => (string) $data['token'],
        ])->save();

        return $attempt;
    }

    public function checkoutUrl(string $iframeToken): string
    {
        return PaytrConfig::fromConfig()->iframeCheckoutUrl.'/'.$iframeToken;
    }

    /**
     * @param  array<string, mixed>  $payload
     * @return array<string, mixed>
     */
    private function redactPayload(array $payload): array
    {
        unset($payload['paytr_token']);

        return $payload;
    }
}
