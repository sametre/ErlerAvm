<?php

namespace App\Http\Controllers\Storefront;

use App\Http\Controllers\Controller;
use Inertia\Inertia;
use Inertia\Response;

class OrderResultController extends Controller
{
    public function success(): Response
    {
        return Inertia::render('Storefront/OrderSuccess');
    }

    public function failed(): Response
    {
        return Inertia::render('Storefront/OrderFailed');
    }
}
