<?php

namespace App\Http\Responses\Concerns;

use Illuminate\Http\Request;

trait RedirectsToCurrentTeam
{
    protected function redirectPathForCurrentTeam(Request $request, string $redirect): string
    {
        $user = $request->user();

        if ($user && $user->isAdmin()) {
            return route('admin.dashboard');
        }

        return $redirect === '/dashboard' ? '/account' : $redirect;
    }
}
