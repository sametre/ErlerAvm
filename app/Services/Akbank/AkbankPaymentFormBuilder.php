<?php

namespace App\Services\Akbank;

use App\Models\Order;
use App\Models\PaymentAttempt;
use Illuminate\Support\Str;

class AkbankPaymentFormBuilder
{
    public function __construct(
        private readonly AkbankSignature $signature,
    ) {}

    /**
     * @return array<string, string>
     */
    public function fields(Order $order, string $merchantOid, int $amountMinor): array
    {
        $config = AkbankConfig::fromConfig();
        $config->assertReady();

        $fields = [
            'clientid' => (string) $config->merchantId,
            'oid' => $merchantOid,
            'amount' => AkbankMoney::toMajor($amountMinor),
            'okUrl' => (string) $config->successUrl,
            'failUrl' => (string) $config->failUrl,
            'islemtipi' => $config->transactionType,
            'taksit' => '',
            'rnd' => Str::random(24),
            'storetype' => $config->paymentModel,
            'currency' => $config->currencyCode,
            'lang' => $config->language,
            'firmaadi' => (string) config('app.name', 'ErlerAVM'),
            'email' => $order->email,
        ];

        $fields['hash'] = $this->signature->formHash($config, $fields);

        return $fields;
    }

    /**
     * @return array{action: string, method: string, fields: array<string, mixed>}
     */
    public function form(PaymentAttempt $attempt): array
    {
        $config = AkbankConfig::fromConfig();
        $config->assertReady();

        return [
            'action' => (string) $config->gatewayUrl,
            'method' => 'POST',
            'fields' => $attempt->request_payload ?? [],
        ];
    }
}
