<?php

namespace App\Http\Controllers\Storefront;

use App\Http\Controllers\Controller;
use App\Models\CorporatePage;
use Inertia\Inertia;
use Inertia\Response;

class ContactController extends Controller
{
    public function __invoke(): Response
    {
        $page = CorporatePage::where('slug', 'iletisim')->first();

        return Inertia::render('Contact/Show', [
            'implementationStatus' => 'merchant_identity_pending',
            'corporateContent' => $page && $page->is_active ? $page->content : null,
        ]);
    }
}
