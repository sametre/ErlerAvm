<?php

namespace App\Services\Akbank;

class AkbankSignature
{
    /**
     * @param  array<string, mixed>  $fields
     */
    public function formHash(AkbankConfig $config, array $fields): string
    {
        $hashString = '';

        foreach ($config->requestHashFields as $field) {
            $hashString .= (string) ($fields[$field] ?? '');
        }

        return base64_encode(sha1($hashString.$config->secretKey, true));
    }

    /**
     * @param  array<string, mixed>  $payload
     */
    public function verifyCallback(AkbankConfig $config, array $payload): bool
    {
        $hash = (string) ($payload['HASH'] ?? $payload['hash'] ?? '');
        $hashParams = (string) ($payload['HASHPARAMS'] ?? '');

        if ($hash === '' || $hashParams === '') {
            return false;
        }

        $hashParamsValue = '';

        foreach (explode(':', $hashParams) as $field) {
            if ($field === '') {
                continue;
            }

            $hashParamsValue .= (string) ($payload[$field] ?? '');
        }

        $providedHashParamsValue = (string) ($payload['HASHPARAMSVAL'] ?? '');

        if ($providedHashParamsValue !== '' && ! hash_equals($providedHashParamsValue, $hashParamsValue)) {
            return false;
        }

        $expected = base64_encode(sha1($hashParamsValue.$config->secretKey, true));

        return hash_equals($expected, $hash);
    }
}
