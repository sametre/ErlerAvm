<?php

use App\Models\BlogPost;
use App\Models\Campaign;
use App\Models\Category;
use App\Models\Order;
use App\Models\Product;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\URL;
use Inertia\Testing\AssertableInertia as Assert;

uses(RefreshDatabase::class);

test('home page still renders before optional storefront management tables are migrated', function () {
    Schema::dropIfExists('storefront_navigation_items');
    Schema::dropIfExists('storefront_banners');

    $this->get(route('home'))
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page->component('Welcome'));
});

test('product urls use seo slug and public ulid instead of database ids', function () {
    $product = Product::query()->create([
        'name' => 'Samsung Galaxy S25 Ultra',
        'slug' => 'samsung-galaxy-s25-ultra',
        'price' => 49999.90,
        'stock_quantity' => 5,
        'status' => 'published',
        'published_at' => now(),
    ]);

    expect($product->url)
        ->toContain('/urun/samsung-galaxy-s25-ultra-'.$product->public_id)
        ->not->toContain('/'.$product->id);

    $this->get($product->url)
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page
            ->component('Storefront/ProductShow')
            ->where('product.public_id', $product->public_id)
        );
});

test('product urls redirect stale slugs to canonical urls', function () {
    $product = Product::query()->create([
        'name' => 'Apple iPhone 15 Pro Max',
        'slug' => 'apple-iphone-15-pro-max',
        'price' => 69999.90,
        'stock_quantity' => 3,
        'status' => 'published',
        'published_at' => now(),
    ]);

    $this->get(route('products.show', [
        'product' => 'yanlis-slug-'.$product->public_id,
    ]))->assertRedirect($product->url);
});

test('published products can be added to the session cart', function () {
    $product = Product::query()->create([
        'name' => 'Erler Test Telefon',
        'slug' => 'erler-test-telefon',
        'price' => 19999.90,
        'stock_quantity' => 5,
        'status' => 'published',
        'published_at' => now(),
    ]);

    $this->post(route('cart.add', ['product' => $product->public_id]), [
        'quantity' => 2,
    ])->assertRedirect();

    $this->get(route('cart.show'))
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page
            ->component('Storefront/Cart')
            ->where('cart.count', 2)
            ->where('cart.items.0.public_id', $product->public_id)
        );
});

test('out of stock products are not added to the session cart', function () {
    $product = Product::query()->create([
        'name' => 'Stoksuz Ürün',
        'slug' => 'stoksuz-urun',
        'price' => 999.90,
        'stock_quantity' => 0,
        'status' => 'published',
        'published_at' => now(),
    ]);

    $this->post(route('cart.add', ['product' => $product->public_id]), [
        'quantity' => 1,
    ])->assertRedirect();

    $this->get(route('cart.show'))
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page
            ->component('Storefront/Cart')
            ->where('cart.count', 0)
            ->where('cart.items', [])
        );
});

test('checkout creates an order with billing shipping and order items', function () {
    $user = User::factory()->create();
    $product = Product::query()->create([
        'name' => 'Erler Test Buzdolabı',
        'slug' => 'erler-test-buzdolabi',
        'sku' => 'TEST-BZD-001',
        'price' => 10000,
        'stock_quantity' => 4,
        'status' => 'published',
        'published_at' => now(),
    ]);

    $this->actingAs($user)
        ->post(route('cart.add', ['product' => $product->public_id]), [
            'quantity' => 2,
        ])
        ->assertRedirect();

    $this->actingAs($user)
        ->post(route('checkout.store'), [
            'shipping_full_name' => 'Ali Veli',
            'shipping_phone' => '05554443322',
            'shipping_city' => 'Bursa',
            'shipping_district' => 'Karacabey',
            'shipping_address_line' => 'Runguşpaşa Mahallesi Bursa Caddesi No:87',
            'shipping_postal_code' => '16700',
            'billing_type' => 'corporate',
            'billing_same_as_shipping' => true,
            'billing_company_name' => 'Erler Test Ltd Şti',
            'billing_tax_number' => '1234567890',
            'billing_tax_office' => 'Karacabey',
        ])
        ->assertRedirect();

    $order = Order::query()->with('items')->firstOrFail();

    expect($order->user_id)->toBe($user->id)
        ->and($order->status)->toBe('payment_pending')
        ->and($order->payment_status)->toBe('pending')
        ->and($order->billing_address['type'])->toBe('corporate')
        ->and($order->items)->toHaveCount(1)
        ->and($order->items->first()->quantity)->toBe(2);

    expect($product->refresh()->stock_quantity)->toBe(2);
});

test('checkout does not create an order from an empty cart', function () {
    $user = User::factory()->create();

    $this->actingAs($user)
        ->post(route('checkout.store'), [
            'shipping_full_name' => 'Ali Veli',
            'shipping_phone' => '05554443322',
            'shipping_city' => 'Bursa',
            'shipping_district' => 'Karacabey',
            'shipping_address_line' => 'Runguşpaşa Mahallesi Bursa Caddesi No:87',
            'billing_type' => 'individual',
            'billing_same_as_shipping' => true,
            'billing_full_name' => 'Ali Veli',
            'billing_identity_number' => '12345678901',
        ])
        ->assertRedirect();

    expect(Order::query()->count())->toBe(0);
});

test('category campaign blog and legal pages render through inertia', function () {
    $category = Category::query()->create([
        'name' => 'Erkek Spor Ayakkabı',
        'slug' => 'erkek-spor-ayakkabi',
        'status' => 'published',
    ]);

    $campaign = Campaign::query()->create([
        'name' => 'Yaz İndirimi',
        'slug' => 'yaz-indirimi',
        'status' => 'published',
    ]);

    $post = BlogPost::query()->create([
        'title' => 'E-ticarette SEO Nasıl Yapılır',
        'slug' => 'e-ticarette-seo-nasil-yapilir',
        'status' => 'published',
        'published_at' => now(),
    ]);

    $this->get(route('categories.show', ['category' => $category->slug]))
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page->component('Storefront/CategoryShow'));

    $this->get($campaign->url)
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page->component('Storefront/CampaignShow'));

    $this->get(route('blog.show', ['post' => $post->slug]))
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page->component('Storefront/BlogShow'));

    $this->get(route('legal.show', ['page' => 'kvkk-aydinlatma-metni']))
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page->component('Legal/Show'));
});

test('invoice download requires a temporary signed url', function () {
    $user = User::factory()->create();
    $order = Order::query()->create([
        'user_id' => $user->id,
        'order_number' => 'ORD-20260625-TEST',
        'email' => $user->email,
        'status' => 'paid',
        'payment_status' => 'paid',
    ]);

    $this->actingAs($user)
        ->get(route('invoice.download', ['public_id' => $order->public_id]))
        ->assertForbidden();

    $signedUrl = URL::temporarySignedRoute(
        'invoice.download',
        now()->addMinutes(30),
        ['public_id' => $order->public_id],
    );

    $this->actingAs($user)
        ->get($signedUrl)
        ->assertStatus(501);
});
