<?php

namespace App\Http\Controllers\Storefront;

use App\Http\Controllers\Controller;
use App\Http\Requests\Storefront\NewsletterSubscriptionRequest;
use App\Models\NewsletterSubscription;
use Illuminate\Http\RedirectResponse;

class NewsletterSubscriptionController extends Controller
{
    public function store(NewsletterSubscriptionRequest $request): RedirectResponse
    {
        $data = $request->validated();

        NewsletterSubscription::query()->updateOrCreate(
            ['email' => strtolower($data['email'])],
            [
                'source' => $data['source'] ?? 'footer',
                'subscribed_at' => now(),
            ],
        );

        return back()->with('toast', [
            'type' => 'success',
            'message' => 'Bülten aboneliğiniz kaydedildi.',
        ]);
    }
}
