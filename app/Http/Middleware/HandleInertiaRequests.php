<?php

namespace App\Http\Middleware;

use App\Models\Category;
use App\Models\PopupCampaign;
use App\Models\StoreSetting;
use App\Services\Storefront\CartService;
use Illuminate\Http\Request;
use Inertia\Middleware;

class HandleInertiaRequests extends Middleware
{
    /**
     * The root template that's loaded on the first page visit.
     *
     * @see https://inertiajs.com/server-side-setup#root-template
     *
     * @var string
     */
    protected $rootView = 'app';

    /**
     * Determines the current asset version.
     *
     * @see https://inertiajs.com/asset-versioning
     */
    public function version(Request $request): ?string
    {
        return parent::version($request);
    }

    /**
     * Define the props that are shared by default.
     *
     * @see https://inertiajs.com/shared-data
     *
     * @return array<string, mixed>
     */
    public function share(Request $request): array
    {
        $user = $request->user();
        $storeSettings = StoreSetting::publicPayload();
        $logoUrl = (string) ($storeSettings['logo_url'] ?? 'erleravm.png');
        $publicLogoUrl = str_starts_with($logoUrl, 'http://') || str_starts_with($logoUrl, 'https://')
            ? $logoUrl
            : asset(ltrim($logoUrl, '/'));

        return [
            ...parent::share($request),
            'name' => $storeSettings['store_name'] ?? config('app.name'),
            'seo' => [
                'siteName' => $storeSettings['store_name'] ?? config('app.name'),
                'defaultTitle' => $storeSettings['store_name'] ?? config('app.name'),
                'defaultDescription' => 'ErlerAVM güvenli alışveriş, hızlı sipariş takibi ve profesyonel e-ticaret deneyimi sunar.',
                'canonicalUrl' => $request->url(),
                'logoUrl' => $publicLogoUrl,
            ],
            'storeSettings' => $storeSettings,
            'auth' => [
                'user' => $user ? array_merge($user->toArray(), [
                    'roles' => $user->roles->pluck('name'),
                ]) : null,
            ],
            'flash' => [
                'toast' => fn () => $request->session()->get('toast'),
            ],
            'favorites' => fn () => $user ? $user->wishlistItems()->join('products', 'wishlists.product_id', '=', 'products.id')->pluck('products.public_id')->toArray() : [],
            'cart' => fn () => app(CartService::class)->summary(),
            'sidebarOpen' => ! $request->hasCookie('sidebar_state') || $request->cookie('sidebar_state') === 'true',
            'globalCategories' => fn () => Category::query()
                ->whereNull('parent_id')
                ->where('status', 'published')
                ->orderBy('sort_order')
                ->orderBy('name')
                ->limit(10)
                ->with(['children' => function ($query) {
                    $query->where('status', 'published')->orderBy('sort_order');
                }])
                ->get()
                ->map(function ($c) {
                    return [
                        'id' => $c->id,
                        'title' => $c->name,
                        'url' => route('categories.show', $c->slug, false),
                        'description' => $c->description,
                        'image' => $c->image_url,
                        'children' => $c->children->map(function ($child) {
                            return [
                                'id' => $child->id,
                                'title' => $child->name,
                                'url' => route('categories.show', $child->slug, false),
                            ];
                        })->toArray(),
                    ];
                }),
            'popupCampaign' => fn () => PopupCampaign::where('is_active', true)->first(),
        ];
    }
}
