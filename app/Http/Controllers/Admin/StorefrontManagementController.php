<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\StorefrontBannerRequest;
use App\Http\Requests\Admin\StorefrontCampaignRequest;
use App\Http\Requests\Admin\StorefrontCategoryRequest;
use App\Http\Requests\Admin\StorefrontNavigationItemRequest;
use App\Models\Campaign;
use App\Models\Category;
use App\Models\StorefrontBanner;
use App\Models\StorefrontNavigationItem;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class StorefrontManagementController extends Controller
{
    public function index(): Response
    {
        return Inertia::render('Admin/Storefront', [
            'campaigns' => Campaign::query()
                ->orderBy('status')
                ->latest()
                ->get(['public_id', 'name', 'slug', 'description', 'status', 'starts_at', 'ends_at', 'updated_at']),
            'categories' => Category::query()
                ->orderBy('sort_order')
                ->orderBy('name')
                ->get(['public_id', 'name', 'slug', 'status', 'sort_order', 'description', 'updated_at']),
        ]);
    }

    public function menuIndex(): Response
    {
        return Inertia::render('Admin/Menu', [
            'navigationItems' => StorefrontNavigationItem::query()
                ->orderBy('location')
                ->orderBy('sort_order')
                ->latest()
                ->get(['public_id', 'location', 'title', 'url', 'status', 'sort_order', 'updated_at']),
        ]);
    }

    public function sliderIndex(): Response
    {
        return Inertia::render('Admin/Slider', [
            'banners' => StorefrontBanner::query()
                ->orderBy('placement')
                ->orderBy('sort_order')
                ->latest()
                ->get(['public_id', 'placement', 'title', 'subtitle', 'cta_label', 'cta_url', 'image_path', 'status', 'sort_order', 'starts_at', 'ends_at', 'updated_at']),
        ]);
    }

    public function destroyNavigation(Request $request, StorefrontNavigationItem $item): RedirectResponse
    {
        $item->delete();

        logger()->info('Storefront navigation item deleted.', [
            'navigation_item_id' => $item->id,
            'admin_id' => $request->user()?->id,
        ]);

        return back()->with('toast', [
            'type' => 'success',
            'message' => 'Menü bağlantısı silindi.',
        ]);
    }

    public function storeNavigation(StorefrontNavigationItemRequest $request): RedirectResponse
    {
        StorefrontNavigationItem::create($request->validated());

        return back()->with('toast', [
            'type' => 'success',
            'message' => 'Menü bağlantısı kaydedildi.',
        ]);
    }

    public function updateNavigation(StorefrontNavigationItemRequest $request, StorefrontNavigationItem $item): RedirectResponse
    {
        $item->update($request->validated());

        return back()->with('toast', [
            'type' => 'success',
            'message' => 'Menü bağlantısı güncellendi.',
        ]);
    }

    public function storeBanner(StorefrontBannerRequest $request): RedirectResponse
    {
        $data = $request->validated();

        if ($request->hasFile('image_path')) {
            $path = $request->file('image_path')->store('banners', 'public');
            $data['image_path'] = '/storage/'.$path;
        }

        StorefrontBanner::create($data);

        return back()->with('toast', [
            'type' => 'success',
            'message' => 'Banner kaydedildi.',
        ]);
    }

    public function updateBanner(StorefrontBannerRequest $request, StorefrontBanner $banner): RedirectResponse
    {
        $data = $request->validated();

        if ($request->hasFile('image_path')) {
            $path = $request->file('image_path')->store('banners', 'public');
            $data['image_path'] = '/storage/'.$path;
        }

        $banner->update($data);

        return back()->with('toast', [
            'type' => 'success',
            'message' => 'Banner güncellendi.',
        ]);
    }

    public function destroyBanner(Request $request, StorefrontBanner $banner): RedirectResponse
    {
        $banner->delete();

        logger()->info('Storefront banner deleted.', [
            'banner_id' => $banner->id,
            'admin_id' => $request->user()?->id,
        ]);

        return back()->with('toast', [
            'type' => 'success',
            'message' => 'Banner silindi.',
        ]);
    }

    public function storeCampaign(StorefrontCampaignRequest $request): RedirectResponse
    {
        Campaign::create($request->validated());

        return back()->with('toast', [
            'type' => 'success',
            'message' => 'Kampanya kaydedildi.',
        ]);
    }

    public function updateCampaign(StorefrontCampaignRequest $request, Campaign $campaign): RedirectResponse
    {
        $campaign->update($request->validated());

        return back()->with('toast', [
            'type' => 'success',
            'message' => 'Kampanya güncellendi.',
        ]);
    }

    public function destroyCampaign(Request $request, Campaign $campaign): RedirectResponse
    {
        $campaign->delete();

        logger()->info('Campaign deleted from storefront management.', [
            'campaign_id' => $campaign->id,
            'admin_id' => $request->user()?->id,
        ]);

        return back()->with('toast', [
            'type' => 'success',
            'message' => 'Kampanya silindi.',
        ]);
    }

    public function updateCategory(StorefrontCategoryRequest $request, Category $category): RedirectResponse
    {
        $category->update($request->validated());

        return back()->with('toast', [
            'type' => 'success',
            'message' => 'Kategori vitrini güncellendi.',
        ]);
    }
}
