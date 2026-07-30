<?php

namespace App\Services\Akbank;

use Illuminate\Support\Str;

class AkbankPayloadSanitizer
{
    /**
     * @param  array<string, mixed>  $payload
     * @return array<string, mixed>
     */
    public function redact(array $payload): array
    {
        foreach ($payload as $key => $value) {
            if (is_array($value)) {
                $payload[$key] = $this->redact($value);

                continue;
            }

            if ($this->isSensitiveKey((string) $key)) {
                $payload[$key] = '[redacted]';
            }
        }

        return $payload;
    }

    private function isSensitiveKey(string $key): bool
    {
        $normalized = Str::lower($key);

        return Str::contains($normalized, [
            'pan',
            'cardnumber',
            'card_number',
            'kredikarti',
            'cvv',
            'cvc',
            'cv2',
            'cavv',
            'password',
            'secret',
            'pin',
        ]);
    }
}
