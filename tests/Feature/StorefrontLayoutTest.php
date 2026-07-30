<?php

use App\Models\NewsletterSubscription;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

test('dashboard route is removed from storefront customer flow', function () {
    $user = User::factory()->create();

    $this->actingAs($user)
        ->get('/dashboard')
        ->assertNotFound();
});

test('predictable admin entry paths are not exposed on the storefront domain', function () {
    $this->get('/admin')->assertNotFound();
    $this->get('/yonetim')->assertNotFound();
    $this->get('/yonetim/giris')->assertNotFound();
});

test('newsletter subscription stores a real email record', function () {
    $this->post(route('newsletter.store'), [
        'email' => 'musteri@example.com',
        'source' => 'footer',
    ])->assertRedirect();

    expect(NewsletterSubscription::query()->where('email', 'musteri@example.com')->exists())->toBeTrue();
});
