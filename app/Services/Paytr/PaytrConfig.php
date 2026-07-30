<?php

namespace App\Services\Paytr;

use RuntimeException;

readonly class PaytrConfig
{
    public function __construct(
        public string $merchantId,
        public string $merchantKey,
        public string $merchantSalt,
        public string $iframeTokenUrl,
        public string $iframeCheckoutUrl,
        public string $okUrl,
        public string $failUrl,
        public bool $testMode,
        public bool $debugOn,
        public string $currency,
        public string $lang,
        public int $timeoutLimit,
        public int $noInstallment,
        public int $maxInstallment,
    ) {}

    public static function fromConfig(): self
    {
        $config = config('services.paytr');

        foreach (['merchant_id', 'merchant_key', 'merchant_salt'] as $key) {
            if (blank($config[$key] ?? null)) {
                throw new RuntimeException("PayTR configuration is missing: services.paytr.{$key}");
            }
        }

        return new self(
            merchantId: (string) $config['merchant_id'],
            merchantKey: (string) $config['merchant_key'],
            merchantSalt: (string) $config['merchant_salt'],
            iframeTokenUrl: (string) $config['iframe_token_url'],
            iframeCheckoutUrl: rtrim((string) $config['iframe_checkout_url'], '/'),
            okUrl: (string) ($config['ok_url'] ?: url('/odeme/basarili')),
            failUrl: (string) ($config['fail_url'] ?: url('/odeme/basarisiz')),
            testMode: (bool) $config['test_mode'],
            debugOn: (bool) $config['debug_on'],
            currency: (string) $config['currency'],
            lang: (string) $config['lang'],
            timeoutLimit: (int) $config['timeout_limit'],
            noInstallment: (int) $config['no_installment'],
            maxInstallment: (int) $config['max_installment'],
        );
    }
}
