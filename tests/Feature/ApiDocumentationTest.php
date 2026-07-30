<?php

use App\Actions\ApiClients\CreateApiClient;
use App\Models\ApiClient;
use App\Models\User;
use Inertia\Testing\AssertableInertia as Assert;

test('authorized admin can open the developer documentation portal', function () {
    $admin = User::factory()->admin()->create();

    $this->actingAs($admin)
        ->get(route('developer.docs'))
        ->assertSuccessful()
        ->assertInertia(fn (Assert $page) => $page
            ->component('Developer/Documentation')
            ->where('version', 'v1')
            ->where('baseUrl', 'https://api.erleravm.test/api/v1')
            ->has('endpoints', 8)
            ->has('filters')
        );
});

test('guest cannot open protected api documentation', function () {
    $this->get(route('developer.docs'))
        ->assertRedirect(route('developer.portal'));
});

test('api subdomain requires valid client credentials for catalogue requests', function () {
    $creator = User::factory()->admin()->create();
    $credentials = app(CreateApiClient::class)->handle($creator, [
        'name' => 'Test Entegrasyonu',
        'abilities' => ['catalog:read'],
    ]);

    $url = 'http://api.erleravm.test/api/v1/products';

    $this->getJson($url)->assertUnauthorized();

    $this->withHeaders([
        'X-API-Key' => $credentials['client']->key,
        'X-API-Secret' => 'yanlis-secret',
    ])->getJson($url)->assertUnauthorized();

    $this->withHeaders([
        'X-API-Key' => $credentials['client']->key,
        'X-API-Secret' => $credentials['secret'],
    ])->getJson($url)
        ->assertSuccessful()
        ->assertJsonPath('success', true);

    expect($credentials['client']->fresh()->last_used_at)->not->toBeNull();

    $credentials['client']->update(['revoked_at' => now()]);

    $this->withHeaders([
        'X-API-Key' => $credentials['client']->key,
        'X-API-Secret' => $credentials['secret'],
    ])->getJson($url)->assertUnauthorized();
});

test('api client secret is never serialized from the model', function () {
    $client = ApiClient::query()->create([
        'name' => 'Gizli Test',
        'key' => 'erl_live_test',
        'secret_hash' => 'hash-degeri',
        'abilities' => ['catalog:read'],
    ]);

    expect($client->toArray())->not->toHaveKey('secret_hash');
});
