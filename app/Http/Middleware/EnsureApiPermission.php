<?php

namespace App\Http\Middleware;

use App\Models\User;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureApiPermission
{
    /**
     * @param  Closure(Request): Response  $next
     */
    public function handle(Request $request, Closure $next, string $permission): Response
    {
        $user = $request->user();

        if (! $user instanceof User || ! $user->isAdmin()) {
            abort(403, 'Yönetim API erişimi reddedildi.');
        }

        if (! $user->hasRole('Super Admin') && ! $user->hasPermissionTo($permission)) {
            abort(403, 'Bu işlem için yetkiniz bulunmuyor.');
        }

        return $next($request);
    }
}
