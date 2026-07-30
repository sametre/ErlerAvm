<?php

use App\Models\Order;
use App\Models\PaymentAttempt;
use App\Models\Product;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Str;
use Inertia\Testing\AssertableInertia as Assert;

uses(RefreshDatabase::class);

beforeEach(function () {
    config([
        'services.akbank.enabled' => true,
        'services.akbank.environment' => 'test',
        'services.akbank.merchant_id' => '700100000',
        'services.akbank.terminal_id' => 'VP000001',
        'services.akbank.username' => 'api-user',
        'services.akbank.password' => 'api-password',
        'services.akbank.secret_key' => 'akbank-secret',
        'services.akbank.payment_model' => '3D_PAY',
        'services.akbank.transaction_type' => 'Auth',
        'services.akbank.gateway_url' => 'https://sanalpos.test/akbank/3d',
        'services.akbank.success_url' => 'https://erleravm.test/akbank/callback/success',
        'services.akbank.fail_url' => 'https://erleravm.test/akbank/callback/fail',
        'services.akbank.currency' => 'TRY',
        'services.akbank.currency_code' => '949',
        'services.akbank.language' => 'tr',
        'services.akbank.accepted_3d_statuses' => ['1'],
        'services.akbank.request_hash_fields' => ['clientid', 'oid', 'amount', 'okUrl', 'failUrl', 'islemtipi', 'taksit', 'rnd'],
        'services.akbank.card_fields' => [
            'holder_name' => 'cardHolderName',
            'number' => 'pan',
            'expiry_month' => 'Ecom_Payment_Card_ExpDate_Month',
            'expiry_year' => 'Ecom_Payment_Card_ExpDate_Year',
            'cvv' => 'cv2',
            'card_type' => 'cardType',
        ],
    ]);
});

test('customer can open an akbank payment form without storing card fields', function () {
    [$user, $order] = akbankOrder();

    $this->actingAs($user)
        ->get(route('payments.akbank.show', ['order' => $order->public_id]))
        ->assertRedirect();

    $attempt = PaymentAttempt::query()
        ->where('provider', 'akbank')
        ->where('order_id', $order->id)
        ->firstOrFail();

    expect($attempt->merchant_oid)->toBe('AKB-'.$order->order_number)
        ->and($attempt->status)->toBe('form_ready')
        ->and($attempt->payment_amount)->toBe(125000)
        ->and($attempt->request_payload)->toHaveKeys(['clientid', 'oid', 'amount', 'okUrl', 'failUrl', 'hash'])
        ->and($attempt->request_payload)->not->toHaveKeys(['pan', 'cv2', 'cardHolderName']);

    $this->get(route('payments.akbank.attempt', ['paymentAttempt' => $attempt->idempotency_key]))
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page
            ->component('payments/AkbankPay')
            ->where('akbankForm.action', 'https://sanalpos.test/akbank/3d')
            ->where('cardFields.number', 'pan')
            ->where('payment.merchant_oid', 'AKB-'.$order->order_number)
        );
});

test('akbank payment form is private to the customer who owns the order', function () {
    [$user, $order] = akbankOrder();
    $attempt = akbankAttempt($order, $user);

    $this->get(route('payments.akbank.attempt', ['paymentAttempt' => $attempt->idempotency_key]))
        ->assertRedirect(route('login'));

    $this->actingAs(User::factory()->create())
        ->get(route('payments.akbank.attempt', ['paymentAttempt' => $attempt->idempotency_key]))
        ->assertNotFound();
});

test('akbank callback requires an accepted 3d status for successful payment', function () {
    [$user, $order] = akbankOrder();
    $attempt = akbankAttempt($order, $user);
    $payload = akbankPayload($attempt->merchant_oid, [
        'amount' => '1250.00',
        'Response' => 'Approved',
        'ProcReturnCode' => '00',
        'mdStatus' => '',
    ]);

    $this->post(route('akbank.callback', ['result' => 'success']), $payload)->assertRedirect();

    expect($attempt->refresh()->status)->toBe('failed')
        ->and($order->refresh()->payment_status)->toBe('failed');
});

test('akbank callback finalizes a successful payment attempt idempotently', function () {
    [$user, $order] = akbankOrder();
    $attempt = akbankAttempt($order, $user);

    $payload = akbankPayload($attempt->merchant_oid, [
        'amount' => '1250.00',
        'Response' => 'Approved',
        'ProcReturnCode' => '00',
        'mdStatus' => '1',
    ]);

    $this->actingAs($user)
        ->post(route('akbank.callback', ['result' => 'success']), $payload)
        ->assertRedirect(route('account.orders.show', ['public_id' => $order->public_id]));

    expect($attempt->refresh()->status)->toBe('paid')
        ->and($attempt->total_amount)->toBe(125000)
        ->and($attempt->response_code)->toBe('00')
        ->and($attempt->completed_at)->not->toBeNull()
        ->and($order->refresh()->payment_status)->toBe('paid')
        ->and($order->status)->toBe('processing');

    $this->actingAs($user)
        ->post(route('akbank.callback', ['result' => 'success']), $payload)
        ->assertRedirect(route('account.orders.show', ['public_id' => $order->public_id]));

    expect($attempt->refresh()->status)->toBe('paid');
});

test('akbank callback rejects invalid hashes and amount mismatches', function () {
    [$user, $order] = akbankOrder();
    $attempt = akbankAttempt($order, $user);

    $invalidHashPayload = akbankPayload($attempt->merchant_oid, [
        'amount' => '1250.00',
        'Response' => 'Approved',
        'ProcReturnCode' => '00',
        'mdStatus' => '1',
    ]);
    $invalidHashPayload['HASH'] = 'invalid';

    $this->post(route('akbank.callback', ['result' => 'success']), $invalidHashPayload)
        ->assertSessionHasErrors('hash');

    expect($attempt->refresh()->status)->toBe('form_ready');

    $amountMismatchPayload = akbankPayload($attempt->merchant_oid, [
        'amount' => '1.00',
        'Response' => 'Approved',
        'ProcReturnCode' => '00',
        'mdStatus' => '1',
    ]);

    $this->post(route('akbank.callback', ['result' => 'success']), $amountMismatchPayload)
        ->assertSessionHasErrors('amount');

    expect($attempt->refresh()->status)->toBe('form_ready')
        ->and($order->refresh()->payment_status)->toBe('pending');
});

/**
 * @return array{0: User, 1: Order}
 */
function akbankOrder(): array
{
    $user = User::factory()->create(['email' => 'akbank@example.com']);
    $product = Product::query()->create([
        'public_id' => (string) Str::ulid(),
        'name' => 'Akbank Test Ürünü',
        'slug' => 'akbank-test-urunu',
        'sku' => 'AKB-001',
        'price' => '1250.00',
        'stock_quantity' => 10,
        'status' => 'published',
        'published_at' => now(),
    ]);

    $order = Order::query()->create([
        'public_id' => (string) Str::ulid(),
        'order_number' => 'ERL-AKB-1',
        'user_id' => $user->id,
        'email' => $user->email,
        'status' => 'payment_pending',
        'payment_status' => 'pending',
        'subtotal' => '1250.00',
        'shipping_total' => '0.00',
        'discount_total' => '0.00',
        'tax_total' => '0.00',
        'grand_total' => '1250.00',
        'currency' => 'TRY',
    ]);

    $order->items()->create([
        'product_id' => $product->id,
        'product_public_id' => $product->public_id,
        'name' => $product->name,
        'sku' => $product->sku,
        'quantity' => 1,
        'unit_price' => '1250.00',
        'line_total' => '1250.00',
        'snapshot' => ['price' => '1250.00'],
    ]);

    return [$user, $order];
}

function akbankAttempt(Order $order, User $user): PaymentAttempt
{
    return PaymentAttempt::query()->create([
        'provider' => 'akbank',
        'merchant_oid' => 'AKB-'.$order->order_number,
        'idempotency_key' => (string) Str::ulid(),
        'user_id' => $user->id,
        'order_id' => $order->id,
        'email' => $user->email,
        'payment_amount' => 125000,
        'currency' => 'TRY',
        'status' => 'form_ready',
        'request_payload' => ['oid' => 'AKB-'.$order->order_number],
    ]);
}

/**
 * @param  array<string, mixed>  $overrides
 * @return array<string, mixed>
 */
function akbankPayload(string $merchantOid, array $overrides = []): array
{
    $payload = [
        'oid' => $merchantOid,
        'amount' => '1250.00',
        'Response' => 'Approved',
        'ProcReturnCode' => '00',
        'mdStatus' => '1',
        'TransId' => 'TRX-123',
        'AuthCode' => 'AUTH-1',
        'HostRefNum' => 'HOST-1',
        'MaskedPan' => '435509******5232',
        'HASHPARAMS' => 'oid:amount:Response:ProcReturnCode:mdStatus:',
    ];

    $payload = [...$payload, ...$overrides];
    $payload['HASHPARAMSVAL'] = $payload['oid'].$payload['amount'].$payload['Response'].$payload['ProcReturnCode'].$payload['mdStatus'];
    $payload['HASH'] = base64_encode(sha1($payload['HASHPARAMSVAL'].config('services.akbank.secret_key'), true));

    return $payload;
}
