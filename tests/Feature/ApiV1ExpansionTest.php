<?php

use App\Models\Product;
use App\Models\User;
use Illuminate\Support\Str;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

function apiProduct(array $attributes = []): Product
{
    return Product::query()->create([
        'public_id' => (string) Str::ulid(),
        'name' => 'Mobil API Ürünü',
        'slug' => 'mobil-api-urunu-'.Str::lower(Str::random(6)),
        'sku' => 'API-'.Str::upper(Str::random(8)),
        'price' => '1499.90',
        'stock_quantity' => 10,
        'status' => 'published',
        'published_at' => now(),
        ...$attributes,
    ]);
}

test('public product catalogue supports search filters sorting and pagination', function () {
    apiProduct(['name' => 'Aranan Televizyon', 'price' => '2500.00']);
    apiProduct(['name' => 'Başka Ürün', 'price' => '100.00']);

    $this->getJson('/api/v1/products?q=Televizyon&sort=price_desc&per_page=10')
        ->assertSuccessful()
        ->assertJsonPath('data.0.name', 'Aranan Televizyon')
        ->assertJsonPath('meta.total', 1)
        ->assertJsonStructure(['data' => [['public_id', 'name', 'price', 'average_rating', 'review_count']], 'meta']);
});

test('authenticated mobile users can manage profile and wishlist', function () {
    $user = User::factory()->create();
    $product = apiProduct();

    $this->actingAs($user, 'sanctum')
        ->patchJson('/api/v1/profile', [
            'name' => 'Mobil Kullanıcı',
            'email' => 'mobil@example.com',
            'phone' => '05550001122',
        ])
        ->assertSuccessful()
        ->assertJsonPath('data.phone', '05550001122');

    $this->actingAs($user, 'sanctum')
        ->putJson('/api/v1/wishlist/'.$product->public_id)
        ->assertCreated()
        ->assertJsonPath('data.public_id', $product->public_id);

    $this->actingAs($user, 'sanctum')
        ->getJson('/api/v1/wishlist')
        ->assertSuccessful()
        ->assertJsonPath('data.0.public_id', $product->public_id);

    $this->actingAs($user, 'sanctum')
        ->deleteJson('/api/v1/wishlist/'.$product->public_id)
        ->assertSuccessful();

    expect($user->wishlistItems()->count())->toBe(0);
});

test('management api requires both admin token scope and granular permission', function () {
    $permission = Permission::query()->create(['name' => 'users.manage', 'guard_name' => 'web']);
    $role = Role::query()->create(['name' => 'API User Manager', 'guard_name' => 'web']);
    $role->givePermissionTo($permission);

    $manager = User::factory()->create(['is_admin' => true]);
    $manager->assignRole($role);
    $customer = User::factory()->create();

    $token = $manager->createToken('Yönetim testi', ['mobile', 'admin'])->plainTextToken;

    $this->withToken($token)
        ->getJson('/api/v1/admin/users')
        ->assertSuccessful()
        ->assertJsonPath('success', true);

    $this->withToken($token)
        ->patchJson('/api/v1/admin/users/'.$customer->id.'/access', [
            'roles' => [],
            'permissions' => [],
            'is_admin' => false,
        ])
        ->assertSuccessful()
        ->assertJsonPath('data.id', $customer->id);

    $unauthorizedAdmin = User::factory()->create(['is_admin' => true]);
    $unauthorizedToken = $unauthorizedAdmin->createToken('Yetkisiz yönetim', ['mobile', 'admin'])->plainTextToken;

    $this->withToken($unauthorizedToken)
        ->getJson('/api/v1/admin/users')
        ->assertForbidden();
});
