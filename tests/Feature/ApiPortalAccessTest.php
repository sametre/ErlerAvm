<?php

use App\Models\User;
use Inertia\Testing\AssertableInertia as Assert;

test('guest sees the independent api portal login and store contact information', function () {
    $this->get(route('developer.portal'))
        ->assertSuccessful()
        ->assertSessionHas('url.intended', route('developer.api-clients.index'))
        ->assertInertia(fn (Assert $page) => $page
            ->component('Developer/Portal')
            ->where('contactPhone', '0224 334 16 30')
            ->where('contactPhoneUrl', 'tel:+902243341630')
            ->where('loginUrl', 'http://api.erleravm.test/login')
        );
});

test('administrator can sign in from the api domain and continue to control center', function () {
    $admin = User::factory()->admin()->create([
        'password' => 'password',
    ]);

    $this->get(route('developer.portal'));

    $this->post('http://api.erleravm.test/login', [
        'email' => $admin->email,
        'password' => 'password',
    ])->assertRedirect(route('developer.api-clients.index'));

    $this->assertAuthenticatedAs($admin);
});

test('administrator is redirected from api portal to its independent control center', function () {
    $admin = User::factory()->admin()->create();

    $this->actingAs($admin)
        ->get(route('developer.portal'))
        ->assertRedirect(route('developer.api-clients.index'));
});

test('regular authenticated customer cannot access the api portal', function () {
    $customer = User::factory()->create(['is_admin' => false]);

    $this->actingAs($customer)
        ->get(route('developer.portal'))
        ->assertNotFound();
});
