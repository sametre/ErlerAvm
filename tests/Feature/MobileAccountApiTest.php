<?php

use App\Models\Cart;
use App\Models\CustomerAddress;
use App\Models\Order;
use App\Models\PaymentAttempt;
use App\Models\Product;
use App\Models\PushDeviceToken;
use App\Models\User;
use App\Models\UserNotification;
use Illuminate\Support\Str;

test('mobile app can register and login through the auth api', function () {
    $email = 'Mobile.Auth@example.com';

    $this->postJson('/api/v1/auth/register', [
        'name' => '  Mobil Müşteri  ',
        'email' => $email,
        'password' => 'SecurePassword123!',
        'password_confirmation' => 'SecurePassword123!',
    ])
        ->assertCreated()
        ->assertJsonPath('data.user.name', 'Mobil Müşteri')
        ->assertJsonPath('data.user.email', 'mobile.auth@example.com')
        ->assertJsonStructure([
            'data' => [
                'user' => ['id', 'name', 'email', 'is_admin'],
                'token',
            ],
        ]);

    $this->postJson('/api/v1/auth/login', [
        'email' => '  MOBILE.AUTH@example.com  ',
        'password' => 'SecurePassword123!',
    ])
        ->assertOk()
        ->assertJsonPath('data.user.email', 'mobile.auth@example.com')
        ->assertJsonStructure([
            'data' => [
                'user' => ['id', 'name', 'email', 'is_admin'],
                'token',
            ],
        ]);
});

test('mobile login rejects invalid credentials with a json error', function () {
    User::factory()->create([
        'email' => 'mobile-invalid@example.com',
        'password' => bcrypt('CorrectPassword123!'),
    ]);

    $this->postJson('/api/v1/auth/login', [
        'email' => 'mobile-invalid@example.com',
        'password' => 'WrongPassword123!',
    ])
        ->assertUnauthorized()
        ->assertJsonPath('success', false)
        ->assertJsonPath('message', 'E-posta adresi veya şifre hatalı.');
});

test('authenticated users can manage only their own addresses through the mobile api', function () {
    $user = User::factory()->create();
    $otherUser = User::factory()->create();

    $this->actingAs($user, 'sanctum')
        ->postJson('/api/v1/addresses', [
            'type' => 'individual',
            'title' => 'Ev',
            'full_name' => 'Ali Veli',
            'phone' => '05554443322',
            'city' => 'Bursa',
            'district' => 'Karacabey',
            'postal_code' => '16700',
            'address_line' => 'Runguşpaşa Mahallesi Bursa Caddesi No 87',
            'is_default' => false,
        ])
        ->assertCreated()
        ->assertJsonPath('data.title', 'Ev')
        ->assertJsonPath('data.is_default', true);

    $address = CustomerAddress::query()->where('user_id', $user->id)->firstOrFail();

    $this->actingAs($otherUser, 'sanctum')
        ->getJson('/api/v1/addresses/'.$address->public_id)
        ->assertNotFound();

    $this->actingAs($user, 'sanctum')
        ->patchJson('/api/v1/addresses/'.$address->public_id, [
            'type' => 'individual',
            'title' => 'İş',
            'full_name' => 'Ali Veli',
            'phone' => '05554443322',
            'city' => 'Bursa',
            'district' => 'Nilüfer',
            'postal_code' => '16000',
            'address_line' => 'Organize Sanayi Bölgesi gerçek teslimat adresi',
            'is_default' => true,
        ])
        ->assertOk()
        ->assertJsonPath('data.title', 'İş')
        ->assertJsonPath('data.district', 'Nilüfer');

    expect(UserNotification::query()->where('user_id', $user->id)->count())->toBe(2);
});

test('mobile notifications can be listed and marked as read', function () {
    $user = User::factory()->create();

    $notification = UserNotification::query()->create([
        'user_id' => $user->id,
        'type' => 'order.created',
        'title' => 'Siparişiniz alındı',
        'body' => 'ERL-API-TEST siparişiniz oluşturuldu.',
        'action_url' => '/hesabim/siparislerim/test',
    ]);

    $this->actingAs($user, 'sanctum')
        ->getJson('/api/v1/notifications')
        ->assertOk()
        ->assertJsonPath('data.unread_count', 1)
        ->assertJsonPath('data.notifications.0.public_id', $notification->public_id);

    $this->actingAs($user, 'sanctum')
        ->patchJson('/api/v1/notifications/'.$notification->public_id.'/read')
        ->assertOk()
        ->assertJsonPath('data.public_id', $notification->public_id);

    expect($notification->refresh()->read_at)->not->toBeNull();
});

test('mobile cart crud can create a checkout order through the backend api', function () {
    $user = User::factory()->create(['email' => 'mobile-checkout@example.com']);
    $product = Product::query()->create([
        'public_id' => (string) Str::ulid(),
        'name' => 'Mobil Checkout Ürünü',
        'slug' => 'mobil-checkout-urunu',
        'sku' => 'MOB-CHK-001',
        'price' => '1750.00',
        'stock_quantity' => 8,
        'status' => 'published',
        'published_at' => now(),
        'images' => ['/images/products/mobile-checkout.png'],
    ]);

    $this->actingAs($user, 'sanctum')
        ->postJson('/api/v1/cart', [
            'public_id' => $product->public_id,
            'quantity' => 2,
        ])
        ->assertOk()
        ->assertJsonPath('data.count', 2)
        ->assertJsonPath('data.items.0.public_id', $product->public_id);

    $this->actingAs($user, 'sanctum')
        ->patchJson('/api/v1/cart/'.$product->public_id, ['quantity' => 3])
        ->assertOk()
        ->assertJsonPath('data.count', 3)
        ->assertJsonPath('data.items.0.quantity', 3);

    $this->actingAs($user, 'sanctum')
        ->deleteJson('/api/v1/cart/'.$product->public_id)
        ->assertOk()
        ->assertJsonPath('data.count', 0);

    $this->actingAs($user, 'sanctum')
        ->postJson('/api/v1/cart', [
            'public_id' => $product->public_id,
            'quantity' => 2,
        ])
        ->assertOk()
        ->assertJsonPath('data.count', 2);

    $response = $this->actingAs($user, 'sanctum')
        ->postJson('/api/v1/checkout', [
            'shipping_full_name' => 'Mobil Müşteri',
            'shipping_phone' => '05554443322',
            'shipping_city' => 'Bursa',
            'shipping_district' => 'Karacabey',
            'shipping_postal_code' => '16700',
            'shipping_address_line' => 'Runguşpaşa Mahallesi Bursa Caddesi No 87',
            'billing_same_as_shipping' => true,
            'billing_type' => 'individual',
            'billing_full_name' => 'Mobil Müşteri',
            'billing_identity_number' => '12345678901',
            'notes' => 'Mobil uygulama test siparişi',
        ])
        ->assertCreated()
        ->assertJsonPath('data.order.payment_required', true)
        ->assertJsonPath('data.order.currency', 'TRY');

    $order = Order::query()
        ->where('user_id', $user->id)
        ->where('order_number', $response->json('data.order.order_number'))
        ->firstOrFail();

    expect($order->items()->count())->toBe(1)
        ->and($order->items()->first()->quantity)->toBe(2)
        ->and($order->billing_address['identity_number'])->toBe('12345678901')
        ->and($product->refresh()->stock_quantity)->toBe(6)
        ->and(Cart::query()->where('user_id', $user->id)->count())->toBe(0);
});

test('mobile app can register push device tokens without duplicating them', function () {
    $user = User::factory()->create();

    $payload = [
        'platform' => 'ios',
        'token' => str_repeat('a', 64),
        'device_id' => 'ios-device-1',
        'app_version' => '1.0.0',
        'locale' => 'tr_TR',
        'timezone' => 'Europe/Istanbul',
    ];

    $this->actingAs($user, 'sanctum')
        ->postJson('/api/v1/push-device-tokens', $payload)
        ->assertCreated()
        ->assertJsonPath('data.registered', true);

    $this->actingAs($user, 'sanctum')
        ->postJson('/api/v1/push-device-tokens', [...$payload, 'app_version' => '1.0.1'])
        ->assertOk()
        ->assertJsonPath('data.registered', true);

    expect(PushDeviceToken::query()->count())->toBe(1)
        ->and(PushDeviceToken::query()->first()->app_version)->toBe('1.0.1');
});

test('mobile app can start an akbank payment for its own pending order', function () {
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

    $user = User::factory()->create(['email' => 'mobile@example.com']);
    $product = Product::query()->create([
        'public_id' => (string) Str::ulid(),
        'name' => 'Mobil Test Ürünü',
        'slug' => 'mobil-test-urunu',
        'sku' => 'MOB-001',
        'price' => 1250,
        'stock_quantity' => 10,
        'status' => 'published',
        'published_at' => now(),
    ]);
    $order = Order::query()->create([
        'public_id' => (string) Str::ulid(),
        'order_number' => 'ERL-API-PAY-1',
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
        'shipping_address' => [
            'full_name' => 'Mobil Müşteri',
            'phone' => '05554443322',
            'address_line' => 'Karacabey teslimat adresi',
        ],
        'billing_address' => [
            'type' => 'individual',
            'same_as_shipping' => true,
        ],
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

    $response = $this->actingAs($user, 'sanctum')
        ->postJson('/api/v1/orders/'.$order->public_id.'/payment')
        ->assertOk()
        ->assertJsonPath('data.payment_required', true)
        ->assertJsonPath('data.provider', 'akbank');

    $attempt = PaymentAttempt::query()
        ->where('provider', 'akbank')
        ->where('order_id', $order->id)
        ->firstOrFail();

    expect($response->json('data.checkout_url'))
        ->toBe(route('payments.akbank.attempt', ['paymentAttempt' => $attempt->idempotency_key]));
});
