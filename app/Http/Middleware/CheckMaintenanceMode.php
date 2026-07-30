<?php

namespace App\Http\Middleware;

use App\Models\StoreSetting;
use Closure;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Symfony\Component\HttpFoundation\Response;

class CheckMaintenanceMode
{
    /**
     * Handle an incoming request.
     *
     * @param  Closure(Request): (Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        // Don't apply to admin routes or inertia's /login etc.
        if ($request->getHost() === config('admin.domain') || $request->is('admin*') || $request->is('login') || $request->is('logout')) {
            return $next($request);
        }

        $settings = StoreSetting::publicPayload();

        if (isset($settings['maintenance_mode_active']) && $settings['maintenance_mode_active']) {
            return Inertia::render('Maintenance')->toResponse($request)->setStatusCode(503);
        }

        return $next($request);
    }
}
