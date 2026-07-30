<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Middleware\EnsureAdminPinVerified;
use App\Http\Requests\Admin\AdminPinChallengeRequest;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Validation\ValidationException;
use Inertia\Inertia;
use Inertia\Response;

class AdminPinChallengeController extends Controller
{
    public function create(Request $request): Response|RedirectResponse
    {
        if (EnsureAdminPinVerified::isVerified($request)) {
            return redirect()->intended(route('admin.dashboard'));
        }

        return Inertia::render('Admin/PinChallenge', [
            'attemptsRemaining' => RateLimiter::remaining(
                $this->throttleKey($request),
                (int) config('admin.pin_max_attempts'),
            ),
            'lockoutSeconds' => RateLimiter::availableIn($this->throttleKey($request)),
            'adminUrl' => (string) config('admin.url'),
            'storefrontUrl' => (string) config('app.url'),
        ]);
    }

    public function store(AdminPinChallengeRequest $request): RedirectResponse
    {
        $this->ensureNotLocked($request);

        $user = $request->user();

        abort_unless($user instanceof User && $user->isAdmin(), 404);

        if (! is_string($user->admin_pin_hash) || $user->admin_pin_hash === '') {
            logger()->critical('Admin PIN is not configured for an admin user.', [
                'user_id' => $user->id,
                'email' => $user->email,
                'ip' => $request->ip(),
            ]);

            throw ValidationException::withMessages([
                'code' => 'Bu yönetici için PIN tanımlı değil. Sistem yöneticisi PIN oluşturmalıdır.',
            ]);
        }

        if (! Hash::check((string) $request->validated('code'), $user->admin_pin_hash)) {
            RateLimiter::hit($this->throttleKey($request), (int) config('admin.pin_decay_seconds'));

            logger()->warning('Invalid admin PIN attempt.', [
                'user_id' => $user->id,
                'ip' => $request->ip(),
                'remaining_attempts' => RateLimiter::remaining(
                    $this->throttleKey($request),
                    (int) config('admin.pin_max_attempts'),
                ),
            ]);

            throw ValidationException::withMessages([
                'code' => 'Kod hatalı.',
            ]);
        }

        RateLimiter::clear($this->throttleKey($request));

        $request->session()->regenerate();
        EnsureAdminPinVerified::markVerified($request);

        $user->forceFill([
            'admin_2fa_verified_at' => now(),
        ])->save();

        logger()->info('Admin PIN verified.', [
            'user_id' => $user->id,
            'ip' => $request->ip(),
        ]);

        return redirect()->intended(route('admin.dashboard'));
    }

    private function ensureNotLocked(Request $request): void
    {
        if (! RateLimiter::tooManyAttempts($this->throttleKey($request), (int) config('admin.pin_max_attempts'))) {
            return;
        }

        throw ValidationException::withMessages([
            'code' => 'Çok fazla hatalı deneme yapıldı. '.RateLimiter::availableIn($this->throttleKey($request)).' saniye sonra tekrar deneyin.',
        ])->status(429);
    }

    private function throttleKey(Request $request): string
    {
        return 'admin-pin:'.$request->user()?->id;
    }
}
