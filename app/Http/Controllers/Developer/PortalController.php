<?php

namespace App\Http\Controllers\Developer;

use App\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class PortalController extends Controller
{
    public function __invoke(Request $request): Response|RedirectResponse
    {
        if ($request->user()?->isAdmin()) {
            return redirect()->route('developer.api-clients.index');
        }

        if ($request->user() !== null) {
            abort(404);
        }

        $request->session()->put('url.intended', route('developer.api-clients.index'));

        return Inertia::render('Developer/Portal', [
            'loginUrl' => $request->getSchemeAndHttpHost().'/login',
            'contactPhone' => '0224 334 16 30',
            'contactPhoneUrl' => 'tel:+902243341630',
        ]);
    }
}
