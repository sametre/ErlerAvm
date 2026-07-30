<?php

use App\Models\User;
use Inertia\Testing\AssertableInertia as Assert;

test('predictable admin paths are hidden on the storefront domain', function () {
    $this->get('/admin')->assertNotFound();
    $this->get('/yonetim')->assertNotFound();
    $this->get('/yonetim/giris')->assertNotFound();
});

test('admin subdomain rejects normal customers with a not found response', function () {
    $user = User::factory()->create();

    $this->actingAs($user)
        ->get(route('admin.dashboard'))
        ->assertNotFound();
});

test('admin user must pass pin challenge before dashboard opens', function () {
    $user = User::factory()->admin()->withAdminPin()->create();

    $this->actingAs($user)
        ->get(route('admin.dashboard'))
        ->assertRedirect(route('admin.pin.create'));
});

test('admin user with verified pin session can access dashboard', function () {
    $user = User::factory()->admin()->withAdminPin()->create();

    $this->actingAs($user)
        ->withSession([config('admin.pin_session_key') => true])
        ->get(route('admin.dashboard'))
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page
            ->component('Admin/Dashboard')
            ->has('metrics')
        );
});
