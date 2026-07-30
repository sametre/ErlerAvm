<?php

namespace App\Services\Paytr;

class PaytrHash
{
    /**
     * @param  array<string, string>  $payload
     */
    public function iframeToken(PaytrConfig $config, array $payload): string
    {
        $hashString = $config->merchantId
            .$payload['user_ip']
            .$payload['merchant_oid']
            .$payload['email']
            .$payload['payment_amount']
            .$payload['user_basket']
            .$payload['no_installment']
            .$payload['max_installment']
            .$payload['currency']
            .$payload['test_mode'];

        return base64_encode(hash_hmac('sha256', $hashString.$config->merchantSalt, $config->merchantKey, true));
    }

    public function callback(PaytrConfig $config, string $merchantOid, string $status, string $totalAmount): string
    {
        return base64_encode(hash_hmac(
            'sha256',
            $merchantOid.$config->merchantSalt.$status.$totalAmount,
            $config->merchantKey,
            true,
        ));
    }

    /**
     * @param  array<string, mixed>  $payload
     */
    public function verifyCallback(PaytrConfig $config, array $payload): bool
    {
        $expected = $this->callback(
            $config,
            (string) $payload['merchant_oid'],
            (string) $payload['status'],
            (string) $payload['total_amount'],
        );

        return hash_equals($expected, (string) $payload['hash']);
    }
}
