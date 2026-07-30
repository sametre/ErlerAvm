<?php

use App\Models\CorporatePage;
use App\Models\Order;
use App\Models\Product;
use App\Models\ProductReview;
use App\Models\Shipment;
use App\Models\ShipmentProvider;
use App\Models\User;
use Inertia\Testing\AssertableInertia as Assert;

test('admin can view and update an order detail', function () {
    $admin = User::factory()->admin()->withAdminPin()->create();
    $order = Order::query()->create([
        'order_number' => 'ERL-TEST-000001',
        'email' => 'customer@example.com',
        'status' => 'payment_pending',
        'payment_status' => 'pending',
        'subtotal' => '100.00',
        'shipping_total' => '0.00',
        'discount_total' => '0.00',
        'tax_total' => '0.00',
        'grand_total' => '100.00',
        'currency' => 'TRY',
        'shipping_address' => ['city' => 'Bursa', 'district' => 'Karacabey'],
        'billing_address' => ['type' => 'individual', 'full_name' => 'Test Customer'],
    ]);

    $this->actingAs($admin)
        ->withSession([config('admin.pin_session_key') => true])
        ->get(route('admin.orders.show', ['order' => $order->public_id]))
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page
            ->component('Admin/OrderShow')
            ->where('order.order_number', 'ERL-TEST-000001')
        );

    $this->actingAs($admin)
        ->withSession([config('admin.pin_session_key') => true])
        ->patch(route('admin.orders.status.update', ['order' => $order->public_id]), [
            'status' => 'processing',
            'payment_status' => 'paid',
            'admin_note' => 'Payment verified manually.',
        ])
        ->assertRedirect();

    $freshOrder = $order->fresh();

    expect($freshOrder->status)->toBe('processing');
    expect($freshOrder->payment_status)->toBe('paid');
});

test('admin can moderate product reviews', function () {
    $admin = User::factory()->admin()->withAdminPin()->create();
    $customer = User::factory()->create();
    $product = Product::query()->create([
        'name' => 'Review Test Product',
        'slug' => 'review-test-product',
        'sku' => 'REVIEW-TEST-1',
        'price' => 100,
        'stock_quantity' => 2,
        'status' => 'published',
        'published_at' => now(),
    ]);
    $review = ProductReview::query()->create([
        'product_id' => $product->id,
        'user_id' => $customer->id,
        'rating' => 5,
        'comment' => 'This product is useful for my home.',
        'status' => 'pending',
    ]);

    $this->actingAs($admin)
        ->withSession([config('admin.pin_session_key') => true])
        ->get(route('admin.reviews.index'))
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page
            ->component('Admin/Reviews')
            ->has('reviews.data', 1)
        );

    $this->actingAs($admin)
        ->withSession([config('admin.pin_session_key') => true])
        ->patch(route('admin.reviews.update', ['review' => $review->public_id]), [
            'status' => 'approved',
            'admin_reply' => 'Değerli yorumunuz için teşekkür ederiz.',
        ])
        ->assertRedirect();

    expect($review->fresh()->status)->toBe('approved');
    expect($review->fresh()->admin_reply)->toBe('Değerli yorumunuz için teşekkür ederiz.');
});

test('admin can create shipment tracking for a paid order', function () {
    $admin = User::factory()->admin()->withAdminPin()->create();
    $order = Order::query()->create([
        'order_number' => 'ERL-TEST-000002',
        'email' => 'shipment@example.com',
        'status' => 'processing',
        'payment_status' => 'paid',
        'subtotal' => '250.00',
        'shipping_total' => '0.00',
        'discount_total' => '0.00',
        'tax_total' => '0.00',
        'grand_total' => '250.00',
        'currency' => 'TRY',
        'shipping_address' => ['city' => 'Bursa', 'district' => 'Karacabey'],
        'billing_address' => ['type' => 'individual', 'full_name' => 'Shipment Customer'],
    ]);
    $provider = ShipmentProvider::query()->create([
        'name' => 'Aras Kargo',
        'code' => 'aras-test',
        'status' => 'active',
        'tracking_url_template' => 'https://example.test/{tracking_number}',
        'integration_mode' => 'manual',
    ]);

    $this->actingAs($admin)
        ->withSession([config('admin.pin_session_key') => true])
        ->patch(route('admin.shipping.update', ['order' => $order->public_id]), [
            'shipment_provider_id' => $provider->id,
            'tracking_number' => 'TRK123456',
            'status' => 'shipped',
            'admin_note' => 'Paket çıkışı yapıldı.',
            'notify_customer' => true,
        ])
        ->assertRedirect();

    $shipment = Shipment::query()->where('order_id', $order->id)->firstOrFail();

    expect($shipment->tracking_number)->toBe('TRK123456')
        ->and($shipment->customer_notified_at)->not->toBeNull()
        ->and($order->fresh()->status)->toBe('shipped');
});

test('published corporate page content is served from database', function () {
    CorporatePage::query()->create([
        'title' => 'Test Kurumsal',
        'slug' => 'test-kurumsal',
        'content' => '<p>Gerçek kurumsal içerik</p>',
        'status' => 'published',
        'is_active' => true,
        'seo_meta' => ['description' => 'Test açıklama'],
    ]);

    $this->get(route('legal.show', ['page' => 'test-kurumsal']))
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page
            ->component('Legal/Show')
            ->where('title', 'Test Kurumsal')
            ->where('content_html', '<p>Gerçek kurumsal içerik</p>')
        );
});
