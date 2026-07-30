<?php

namespace App\Services\Akbank;

use RuntimeException;

readonly class AkbankConfig
{
    /**
     * @param  array<int, string>  $accepted3dStatuses
     * @param  array<int, string>  $requestHashFields
     * @param  array<string, string>  $cardFields
     */
    public function __construct(
        public bool $enabled,
        public string $environment,
        public ?string $merchantId,
        public ?string $terminalId,
        public ?string $username,
        public ?string $password,
        public ?string $secretKey,
        public string $paymentModel,
        public string $transactionType,
        public ?string $gatewayUrl,
        public ?string $successUrl,
        public ?string $failUrl,
        public string $currency,
        public string $currencyCode,
        public string $language,
        public int $timeoutSeconds,
        public array $accepted3dStatuses,
        public array $requestHashFields,
        public array $cardFields,
    ) {}

    public static function fromConfig(): self
    {
        $config = config('services.akbank');

        return new self(
            enabled: (bool) data_get($config, 'enabled', false),
            environment: (string) data_get($config, 'environment', 'test'),
            merchantId: filled(data_get($config, 'merchant_id')) ? (string) data_get($config, 'merchant_id') : null,
            terminalId: filled(data_get($config, 'terminal_id')) ? (string) data_get($config, 'terminal_id') : null,
            username: filled(data_get($config, 'username')) ? (string) data_get($config, 'username') : null,
            password: filled(data_get($config, 'password')) ? (string) data_get($config, 'password') : null,
            secretKey: filled(data_get($config, 'secret_key')) ? (string) data_get($config, 'secret_key') : null,
            paymentModel: (string) data_get($config, 'payment_model', '3D_PAY'),
            transactionType: (string) data_get($config, 'transaction_type', 'Auth'),
            gatewayUrl: filled(data_get($config, 'gateway_url')) ? (string) data_get($config, 'gateway_url') : null,
            successUrl: filled(data_get($config, 'success_url')) ? (string) data_get($config, 'success_url') : route('akbank.callback', ['result' => 'success']),
            failUrl: filled(data_get($config, 'fail_url')) ? (string) data_get($config, 'fail_url') : route('akbank.callback', ['result' => 'fail']),
            currency: (string) data_get($config, 'currency', 'TRY'),
            currencyCode: (string) data_get($config, 'currency_code', '949'),
            language: (string) data_get($config, 'language', 'tr'),
            timeoutSeconds: (int) data_get($config, 'timeout_seconds', 20),
            accepted3dStatuses: array_values(array_filter((array) data_get($config, 'accepted_3d_statuses', ['1']))),
            requestHashFields: array_values((array) data_get($config, 'request_hash_fields', [])),
            cardFields: (array) data_get($config, 'card_fields', []),
        );
    }

    public function assertReady(): void
    {
        $missing = [];

        if (! $this->enabled) {
            $missing[] = 'AKBANK_ENABLED';
        }

        foreach ([
            'AKBANK_MERCHANT_ID' => $this->merchantId,
            'AKBANK_SECRET_KEY' => $this->secretKey,
            'AKBANK_GATEWAY_URL' => $this->gatewayUrl,
        ] as $key => $value) {
            if (! filled($value)) {
                $missing[] = $key;
            }
        }

        if ($missing !== []) {
            throw new RuntimeException('Akbank Sanal POS yapılandırması eksik: '.implode(', ', $missing));
        }
    }
}
