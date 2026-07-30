<?php

namespace App\Http\Middleware;

use App\Models\User;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureAdminAccess
{
    /**
     * @param  Closure(Request): Response  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();

        if (! $user instanceof User || ! $user->isAdmin()) {
            logger()->warning('Unauthorized admin panel access attempt.', [
                'user_id' => $user instanceof User ? $user->id : null,
                'path' => $request->path(),
                'host' => $request->getHost(),
                'ip' => $request->ip(),
            ]);

            abort(404);
        }

        return $next($request);
    }
}
