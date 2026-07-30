<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureAdminPinVerified
{
    /**
     * Handle an incoming request.
     *
     * @param  Closure(Request): (Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        if (self::isVerified($request)) {
            return $next($request);
        }

        $request->session()->put('url.intended', $request->fullUrl());

        return redirect()->route('admin.pin.create');
    }

    public static function forget(Request $request): void
    {
        $request->session()->forget((string) config('admin.pin_session_key'));
    }

    public static function markVerified(Request $request): void
    {
        $request->session()->put((string) config('admin.pin_session_key'), true);
    }

    public static function isVerified(Request $request): bool
    {
        return (bool) $request->session()->get((string) config('admin.pin_session_key'), false);
    }
}
