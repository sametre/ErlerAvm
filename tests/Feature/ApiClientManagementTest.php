<?php

use App\Models\ApiClient;
use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Inertia\Testing\AssertableInertia as Assert;
use Spatie\Permission\Models\Permission;

test('authorized admin can open api client management', function () {
    Permission::query()->create(['name' => 'api-clients.manage', 'guard_name' => 'web']);
    $admin = User::factory()->admin()->withAdminPin()->create();
    $admin->givePermissionTo('api-clients.manage');

    $this->actingAs($admin)
        ->get(route('developer.api-clients.index'))
        ->assertSuccessful()
        ->assertInertia(fn (Assert $page) => $page
            ->component('Admin/ApiClients')
            ->has('clients')
            ->has('abilities.catalog:read')
        );
});

test('api client creation reveals secret once and stores only its hash', function () {
    Permission::query()->create(['name' => 'api-clients.manage', 'guard_name' => 'web']);
    $admin = User::factory()->admin()->withAdminPin()->create();
    $admin->givePermissionTo('api-clients.manage');

    $response = $this->actingAs($admin)
        ->postJson(route('developer.api-clients.store'), [
            'name' => 'iOS Üretim',
            'abilities' => ['catalog:read'],
            'allowed_ips' => ['127.0.0.1'],
        ])
        ->assertCreated()
        ->assertHeader('Cache-Control', 'no-store, private')
        ->assertJsonStructure([
            'client' => ['public_id', 'name', 'key', 'abilities'],
            'credentials' => ['key', 'secret'],
        ]);

    $client = ApiClient::query()->sole();
    $secret = $response->json('credentials.secret');

    expect($client->secret_hash)->not->toBe($secret)
        ->and(Hash::check($secret, $client->secret_hash))->toBeTrue()
        ->and($client->toArray())->not->toHaveKey('secret_hash');
});

test('admin without granular permission cannot create api clients', function () {
    $admin = User::factory()->admin()->withAdminPin()->create();

    $this->actingAs($admin)
        ->postJson(route('developer.api-clients.store'), [
            'name' => 'Yetkisiz İstemci',
            'abilities' => ['catalog:read'],
        ])
        ->assertForbidden();
});
