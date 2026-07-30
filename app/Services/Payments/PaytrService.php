<?php

namespace App\Services\Payments;

use App\Models\Order;
use Exception;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class PaytrService
{
    public function generateToken(Order $order): string
    {
        $merchant_id = config('paytr.merchant_id');
        $merchant_key = config('paytr.merchant_key');
        $merchant_salt = config('paytr.merchant_salt');

        $email = $order->email ?? auth()->user()->email;
        $payment_amount = $order->grand_total * 100; // Kr kuruş cinsinden
        $merchant_oid = $order->order_number;
        $user_name = $order->shipping_address['full_name'] ?? 'Müşteri';
        $user_address = $order->shipping_address['address_line'] ?? 'Adres Bulunmuyor';
        $user_phone = $order->shipping_address['phone'] ?? '05555555555';
        $merchant_ok_url = route('payments.success');
        $merchant_fail_url = route('payments.fail');

        // Sepetteki ürünler: [["Ürün Adı", "Fiyat", 1]]
        $user_basket = [];
        foreach ($order->items as $item) {
            $user_basket[] = [
                $item->name,
                (string) $item->unit_price,
                $item->quantity,
            ];
        }
        $user_basket = base64_encode(json_encode($user_basket));

        if (empty($order->user_ip)) {
            $user_ip = request()->ip() ?? '127.0.0.1';
        } else {
            $user_ip = $order->user_ip;
        }

        $timeout_limit = config('paytr.timeout_limit');
        $debug_on = config('paytr.debug_on');
        $test_mode = config('paytr.test_mode');
        $no_installment = config('paytr.no_installment');
        $max_installment = config('paytr.max_installment');
        $currency = 'TL';

        $hash_str = $merchant_id.$user_ip.$merchant_oid.$email.$payment_amount.$user_basket.$no_installment.$max_installment.$currency.$test_mode;
        $paytr_token = base64_encode(hash_hmac('sha256', $hash_str.$merchant_salt, $merchant_key, true));

        $post_vals = [
            'merchant_id' => $merchant_id,
            'user_ip' => $user_ip,
            'merchant_oid' => $merchant_oid,
            'email' => $email,
            'payment_amount' => $payment_amount,
            'paytr_token' => $paytr_token,
            'user_basket' => $user_basket,
            'debug_on' => $debug_on,
            'no_installment' => $no_installment,
            'max_installment' => $max_installment,
            'user_name' => $user_name,
            'user_address' => $user_address,
            'user_phone' => $user_phone,
            'merchant_ok_url' => $merchant_ok_url,
            'merchant_fail_url' => $merchant_fail_url,
            'timeout_limit' => $timeout_limit,
            'currency' => $currency,
            'test_mode' => $test_mode,
        ];

        try {
            $response = Http::asForm()->post('https://www.paytr.com/odeme/api/get-token', $post_vals);

            $result = $response->json();

            if ($result['status'] === 'success') {
                return $result['token'];
            }

            Log::error('PayTR Token Error: '.($result['reason'] ?? 'Unknown error'), ['response' => $result]);
            throw new Exception('Ödeme altyapısına bağlanılamadı: '.($result['reason'] ?? 'Bilinmeyen hata'));
        } catch (Exception $e) {
            Log::error('PayTR Request Error: '.$e->getMessage());
            throw new Exception('Ödeme başlatılamadı. Lütfen daha sonra tekrar deneyin.');
        }
    }
}
