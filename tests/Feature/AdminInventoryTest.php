<?php

use App\Models\InventoryMovement;
use App\Models\Product;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

test('checkout requires an authenticated customer before payment', function () {
    $this->get(route('checkout.show'))->assertRedirect(route('login'));
});

test('admin can adjust stock and movement history is recorded', function () {
    $user = User::factory()->admin()->withAdminPin()->create();
    $product = Product::query()->create([
        'name' => 'Test Ürün',
        'slug' => 'test-urun',
        'sku' => 'TEST-STOCK-1',
        'price' => 100,
        'stock_quantity' => 5,
        'status' => 'published',
    ]);

    $this->actingAs($user)
        ->withSession([config('admin.pin_session_key') => true])
        ->post(route('admin.inventory.adjust', [
            'product' => $product->public_id,
        ]), [
            'quantity_delta' => 3,
            'reason' => 'purchase_entry',
            'note' => 'Test mal girişi',
        ])
        ->assertRedirect();

    expect($product->fresh()->stock_quantity)->toBe(8);

    $movement = InventoryMovement::query()->firstOrFail();

    expect($movement->stock_before)->toBe(5)
        ->and($movement->stock_after)->toBe(8)
        ->and($movement->quantity_delta)->toBe(3)
        ->and($movement->user_id)->toBe($user->id);
});

test('admin stock adjustment prevents negative stock', function () {
    $user = User::factory()->admin()->withAdminPin()->create();
    $product = Product::query()->create([
        'name' => 'Negatif Stok Test',
        'slug' => 'negatif-stok-test',
        'sku' => 'TEST-STOCK-2',
        'price' => 100,
        'stock_quantity' => 2,
        'status' => 'published',
    ]);

    $this->actingAs($user)
        ->withSession([config('admin.pin_session_key') => true])
        ->post(route('admin.inventory.adjust', [
            'product' => $product->public_id,
        ]), [
            'quantity_delta' => -3,
            'reason' => 'damage',
        ])
        ->assertStatus(422);

    expect($product->fresh()->stock_quantity)->toBe(2);
    expect(InventoryMovement::query()->count())->toBe(0);
});
