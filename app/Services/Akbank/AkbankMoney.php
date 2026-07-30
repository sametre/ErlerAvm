<?php

namespace App\Services\Akbank;

use InvalidArgumentException;

class AkbankMoney
{
    public static function toMinor(string|int|float $amount): int
    {
        $normalized = str_replace(',', '.', trim((string) $amount));

        if (! preg_match('/^\d+(\.\d{1,2})?$/', $normalized)) {
            throw new InvalidArgumentException('Geçersiz ödeme tutarı.');
        }

        [$major, $minor] = array_pad(explode('.', $normalized, 2), 2, '0');

        return ((int) $major * 100) + (int) str_pad(substr($minor, 0, 2), 2, '0');
    }

    public static function toMajor(int $amountMinor): string
    {
        return number_format($amountMinor / 100, 2, '.', '');
    }
}
