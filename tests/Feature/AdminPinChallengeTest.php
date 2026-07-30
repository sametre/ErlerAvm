<?php

use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\RateLimiter;
use Inertia\Testing\AssertableInertia as Assert;

test('admin pin challenge screen is rendered for admin users', function () {
    $user = User::factory()->admin()->withAdminPin()->create();
    RateLimiter::clear('admin-pin:'.$user->id);

    $this->actingAs($user)
        ->get(route('admin.pin.create'))
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page
            ->component('Admin/PinChallenge')
            ->where('attemptsRemaining', config('admin.pin_max_attempts'))
        );
});

test('correct admin pin opens the dashboard session', function () {
    $user = User::factory()->admin()->withAdminPin('654321')->create();
    RateLimiter::clear('admin-pin:'.$user->id);

    $this->actingAs($user)
        ->post(route('admin.pin.store'), ['code' => '654321'])
        ->assertRedirect(route('admin.dashboard'));

    $this->assertTrue((bool) session()->get(config('admin.pin_session_key'), false));
    expect($user->fresh()->admin_2fa_verified_at)->not->toBeNull();
});

test('incorrect admin pin is rejected and rate limited', function () {
    $user = User::factory()->admin()->withAdminPin('654321')->create();
    RateLimiter::clear('admin-pin:'.$user->id);

    for ($attempt = 1; $attempt <= (int) config('admin.pin_max_attempts'); $attempt++) {
        $this->actingAs($user)
            ->from(route('admin.pin.create'))
            ->post(route('admin.pin.store'), ['code' => '000000'])
            ->assertSessionHasErrors('code');
    }

    expect(RateLimiter::tooManyAttempts('admin-pin:'.$user->id, (int) config('admin.pin_max_attempts')))->toBeTrue();

    $this->actingAs($user)
        ->from(route('admin.pin.create'))
        ->post(route('admin.pin.store'), ['code' => '654321'])
        ->assertSessionHasErrors('code')
        ->assertStatus(302);
});

test('admin user access update stores pin as a hash', function () {
    $admin = User::factory()->admin()->withAdminPin()->create();
    $target = User::factory()->admin()->create();

    $this->actingAs($admin)
        ->withSession([config('admin.pin_session_key') => true])
        ->patch(route('admin.users.update', ['user' => $target->id]), [
            'is_admin' => true,
            'roles' => [],
            'admin_pin' => '112233',
            'admin_pin_confirmation' => '112233',
        ])
        ->assertRedirect();

    $target->refresh();

    expect($target->admin_pin_hash)->not->toBe('112233')
        ->and(Hash::check('112233', (string) $target->admin_pin_hash))->toBeTrue()
        ->and($target->admin_2fa_verified_at)->toBeNull();
});
