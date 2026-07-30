<?php

use App\Models\Order;
use App\Models\PaymentAttempt;
use App\Models\User;
use App\Services\Paytr\PaytrConfig;
use App\Services\Paytr\PaytrHash;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Str;

uses(RefreshDatabase::class);

beforeEach(function () {
    config([
        'services.paytr.merchant_id' => '123456',
        'services.paytr.merchant_key' => 'test-key',
        'services.paytr.merchant_salt' => 'test-salt',
        'services.paytr.iframe_token_url' => 'https://www.paytr.com/odeme/api/get-token',
        'services.paytr.iframe_checkout_url' => 'https://www.paytr.com/odeme/guvenli',
        'services.paytr.ok_url' => 'https://erleravm.test/odeme/basarili',
        'services.paytr.fail_url' => 'https://erleravm.test/odeme/basarisiz',
        'services.paytr.test_mode' => true,
        'services.paytr.debug_on' => false,
        'services.paytr.currency' => 'TL',
        'services.paytr.lang' => 'tr',
        'services.paytr.timeout_limit' => 30,
        'services.paytr.no_installment' => 0,
        'services.paytr.max_installment' => 0,
    ]);
});

test('paytr callback finalizes a successful payment attempt', function () {
    $user = User::factory()->create(['email' => 'musteri@example.com']);
    $order = Order::query()->create([
        'public_id' => (string) Str::ulid(),
        'order_number' => 'ERL-1001',
        'user_id' => $user->id,
        'email' => $user->email,
        'status' => 'payment_pending',
        'payment_status' => 'pending',
        'subtotal' => '125.00',
        'shipping_total' => '0.00',
        'discount_total' => '0.00',
        'tax_total' => '0.00',
        'grand_total' => '125.00',
        'currency' => 'TRY',
    ]);
    $attempt = PaymentAttempt::query()->create([
        'provider' => 'paytr',
        'merchant_oid' => $order->order_number,
        'user_id' => $user->id,
        'order_id' => $order->id,
        'email' => 'musteri@example.com',
        'payment_amount' => 12500,
        'currency' => 'TL',
        'status' => 'pending',
    ]);

    $payload = paytrPayload($attempt->merchant_oid, 'success', 12500);

    $this->post(route('paytr.callback'), $payload)
        ->assertOk()
        ->assertSee('OK', false);

    $attempt->refresh();

    expect($attempt->status)->toBe('paid')
        ->and($attempt->total_amount)->toBe(12500)
        ->and($attempt->notified_at)->not->toBeNull()
        ->and($order->refresh()->payment_status)->toBe('paid')
        ->and($order->status)->toBe('processing');
});

test('paytr callback is idempotent for finalized attempts', function () {
    $attempt = PaymentAttempt::query()->create([
        'provider' => 'paytr',
        'merchant_oid' => 'ERL-1002',
        'email' => 'musteri@example.com',
        'payment_amount' => 12500,
        'total_amount' => 12500,
        'currency' => 'TL',
        'status' => 'paid',
        'completed_at' => now()->subMinute(),
    ]);

    $payload = paytrPayload($attempt->merchant_oid, 'success', 12500);

    $this->post(route('paytr.callback'), $payload)
        ->assertOk()
        ->assertSee('OK', false);

    expect($attempt->refresh()->status)->toBe('paid');
});

test('paytr callback rejects invalid hashes', function () {
    PaymentAttempt::query()->create([
        'provider' => 'paytr',
        'merchant_oid' => 'ERL-1003',
        'email' => 'musteri@example.com',
        'payment_amount' => 12500,
        'currency' => 'TL',
        'status' => 'pending',
    ]);

    $payload = paytrPayload('ERL-1003', 'success', 12500);
    $payload['hash'] = 'invalid-hash';

    $this->post(route('paytr.callback'), $payload)->assertSessionHasErrors('hash');

    expect(PaymentAttempt::query()->where('merchant_oid', 'ERL-1003')->value('status'))->toBe('pending');
});

test('paytr callback rejects successful payments when paid amount does not match requested amount', function () {
    $attempt = PaymentAttempt::query()->create([
        'provider' => 'paytr',
        'merchant_oid' => 'ERL-1004',
        'email' => 'musteri@example.com',
        'payment_amount' => 12500,
        'currency' => 'TL',
        'status' => 'pending',
    ]);

    $payload = paytrPayload($attempt->merchant_oid, 'success', 10000);

    $this->post(route('paytr.callback'), $payload)->assertSessionHasErrors('total_amount');

    $attempt->refresh();

    expect($attempt->status)->toBe('pending')
        ->and($attempt->total_amount)->toBeNull();
});

function paytrPayload(string $merchantOid, string $status, int $totalAmount): array
{
    $config = PaytrConfig::fromConfig();
    $hash = app(PaytrHash::class)->callback($config, $merchantOid, $status, (string) $totalAmount);

    return [
        'merchant_oid' => $merchantOid,
        'status' => $status,
        'total_amount' => $totalAmount,
        'hash' => $hash,
        'test_mode' => 1,
        'payment_type' => 'card',
        'currency' => 'TL',
        'payment_amount' => $totalAmount,
        'installment_count' => 0,
    ];
}
