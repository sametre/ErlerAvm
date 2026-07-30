<?php

use App\Http\Controllers\Account\AccountController;
use App\Http\Controllers\Admin\AdminPinChallengeController;
use App\Http\Controllers\Admin\ApiClientController;
use App\Http\Controllers\Admin\DashboardController as AdminDashboardController;
use App\Http\Controllers\Admin\InventoryController;
use App\Http\Controllers\Admin\OperationsController;
use App\Http\Controllers\Admin\OrderNotificationController;
use App\Http\Controllers\Admin\PopupCampaignController;
use App\Http\Controllers\Admin\StorefrontManagementController;
use App\Http\Controllers\Developer\DocumentationController;
use App\Http\Controllers\Developer\PortalController;
use App\Http\Controllers\Invoices\InvoiceDownloadController;
use App\Http\Controllers\Legal\LegalPageController;
use App\Http\Controllers\Payments\AkbankCallbackController;
use App\Http\Controllers\Payments\AkbankPaymentController;
use App\Http\Controllers\Payments\PaymentReturnController;
use App\Http\Controllers\Payments\PaytrCallbackController;
use App\Http\Controllers\Storefront\BlogController;
use App\Http\Controllers\Storefront\BrandController;
use App\Http\Controllers\Storefront\CampaignController;
use App\Http\Controllers\Storefront\CartController;
use App\Http\Controllers\Storefront\CategoryController;
use App\Http\Controllers\Storefront\CheckoutController;
use App\Http\Controllers\Storefront\ContactController;
use App\Http\Controllers\Storefront\HomeController;
use App\Http\Controllers\Storefront\NewsletterSubscriptionController;
use App\Http\Controllers\Storefront\OrderResultController;
use App\Http\Controllers\Storefront\OrderTrackingController;
use App\Http\Controllers\Storefront\ProductController;
use App\Http\Controllers\Storefront\ProductReviewController;
use App\Http\Controllers\Storefront\SearchController;
use App\Http\Controllers\Storefront\WishlistController;
use App\Models\BlogPost;
use App\Models\Brand;
use App\Models\Campaign;
use App\Models\Category;
use App\Models\CorporatePage;
use App\Models\Product;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Response;
use Illuminate\Support\Facades\Route;

Route::get('/', HomeController::class)->name('home');

Route::domain((string) config('api.domain'))
    ->name('developer.')
    ->group(function () {
        Route::get('/', PortalController::class)->name('portal');

        Route::middleware(['auth', 'verified', 'admin.access'])->group(function () {
            Route::get('rehber', DocumentationController::class)->name('docs');
            Route::get('yonetim', [ApiClientController::class, 'index'])->name('api-clients.index');
            Route::post('yonetim', [ApiClientController::class, 'store'])->name('api-clients.store');
            Route::delete('yonetim/{apiClient:public_id}', [ApiClientController::class, 'destroy'])->name('api-clients.destroy');
        });
    });

Route::get('kategori/{category}', [CategoryController::class, 'show'])->name('categories.show');
Route::get('kargo-hesaplama', function () {
    $page = CorporatePage::where('slug', 'kargo-hesaplama')->first();

    return Inertia\Inertia::render('Storefront/ShippingCalculator', [
        'corporateContent' => $page && $page->is_active ? $page->content : null,
    ]);
})->name('shipping-calculator');
Route::get('sikca-sorulan-sorular', function () {
    $page = CorporatePage::where('slug', 'sikca-sorulan-sorular')->first();

    return Inertia\Inertia::render('Storefront/Faq', [
        'corporateContent' => $page && $page->is_active ? $page->content : null,
    ]);
})->name('faq');
Route::get('urun/{product}', [ProductController::class, 'show'])
    ->where('product', '.+-[0-7][0-9a-hjkmnp-tv-zA-HJKMNP-TV-Z]{25}')
    ->name('products.show');
Route::post('urun/{product:public_id}/yorum', [ProductReviewController::class, 'store'])
    ->middleware(['auth', 'verified'])
    ->name('products.reviews.store');
Route::get('arama', SearchController::class)->name('search');
Route::get('sepet', [CartController::class, 'show'])->name('cart.show');
Route::post('sepet/{product:public_id}/ekle', [CartController::class, 'add'])->name('cart.add');
Route::patch('sepet/{product:public_id}', [CartController::class, 'update'])->name('cart.update');
Route::delete('sepet/{product:public_id}', [CartController::class, 'remove'])->name('cart.remove');
Route::get('siparis/basarili', [OrderResultController::class, 'success'])->name('orders.success');
Route::get('siparis/basarisiz', [OrderResultController::class, 'failed'])->name('orders.failed');
Route::get('siparis-takip/{public_id}', [OrderTrackingController::class, 'show'])
    ->whereUlid('public_id')
    ->name('orders.track');
Route::get('marka/{brand}', [BrandController::class, 'show'])->name('brands.show');
Route::get('kampanya/{campaign}', [CampaignController::class, 'show'])
    ->where('campaign', '.+-[0-7][0-9a-hjkmnp-tv-zA-HJKMNP-TV-Z]{25}')
    ->name('campaigns.show');
Route::get('blog/{post}', [BlogController::class, 'show'])->name('blog.show');
Route::get('iletisim', ContactController::class)->name('contact.show');
Route::get('yasal/{page}', [LegalPageController::class, 'show'])->name('legal.show');
Route::get('banka-hesaplarimiz', fn () => app(LegalPageController::class)->show('banka-hesaplarimiz'))->name('banka-hesaplarimiz');
Route::get('kariyer', fn () => app(LegalPageController::class)->show('kariyer-ve-is-basvurusu'))->name('kariyer');
Route::get('magazalarimiz', fn () => app(LegalPageController::class)->show('magazalarimiz'))->name('magazalarimiz');

Route::post('bulten-abonelik', [NewsletterSubscriptionController::class, 'store'])
    ->middleware('throttle:3,1')
    ->name('newsletter.store');

Route::post('paytr/callback', PaytrCallbackController::class)->name('paytr.callback');
Route::post('akbank/callback/{result}', AkbankCallbackController::class)
    ->whereIn('result', ['success', 'fail'])
    ->name('akbank.callback');
Route::get('odeme/basarili', [PaymentReturnController::class, 'success'])->name('payments.success');
Route::get('odeme/basarisiz', [PaymentReturnController::class, 'fail'])->name('payments.fail');

Route::get('sitemap.xml', function () {
    return Cache::remember('sitemap_xml', now()->addHour(), function () {
        $now = now()->toAtomString();

        $urls = [
            ['loc' => route('home'), 'priority' => '1.0', 'lastmod' => $now],
            ['loc' => route('search'), 'priority' => '0.6'],
            ['loc' => route('contact.show'), 'priority' => '0.5'],
            ['loc' => route('legal.show', ['page' => 'hakkimizda']), 'priority' => '0.5'],
            ['loc' => route('legal.show', ['page' => 'teslimat-ve-kargo']), 'priority' => '0.4'],
            ['loc' => route('legal.show', ['page' => 'guvenli-alisveris']), 'priority' => '0.4'],
            ['loc' => route('legal.show', ['page' => 'satis-kosullari']), 'priority' => '0.4'],
            ['loc' => route('legal.show', ['page' => 'iade-degisim-politikasi']), 'priority' => '0.4'],
            ['loc' => route('legal.show', ['page' => 'mesafeli-satis-sozlesmesi']), 'priority' => '0.4'],
            ['loc' => route('legal.show', ['page' => 'on-bilgilendirme-formu']), 'priority' => '0.4'],
            ['loc' => route('legal.show', ['page' => 'gizlilik-politikasi']), 'priority' => '0.4'],
            ['loc' => route('legal.show', ['page' => 'kvkk-aydinlatma-metni']), 'priority' => '0.4'],
            ['loc' => route('legal.show', ['page' => 'cerez-politikasi']), 'priority' => '0.4'],
            ['loc' => route('legal.show', ['page' => 'uyelik-sozlesmesi']), 'priority' => '0.4'],
            ['loc' => route('login'), 'priority' => '0.3'],
            ['loc' => route('register'), 'priority' => '0.3'],
        ];

        foreach (Category::query()
            ->where('status', 'published')
            ->orderBy('sort_order')
            ->get(['slug', 'updated_at']) as $category) {
            $urls[] = [
                'loc' => route('categories.show', ['category' => $category->slug]),
                'priority' => '0.8',
                'lastmod' => $category->updated_at?->toAtomString(),
            ];
        }

        foreach (Product::query()
            ->where('status', 'published')
            ->whereNotNull('published_at')
            ->latest('published_at')
            ->get(['slug', 'public_id', 'name', 'images', 'updated_at']) as $product) {
            $urls[] = [
                'loc' => $product->url,
                'priority' => '0.9',
                'lastmod' => $product->updated_at?->toAtomString(),
                'image' => filled($product->images[0] ?? null)
                    ? asset(ltrim((string) $product->images[0], '/'))
                    : null,
                'image_title' => $product->name,
            ];
        }

        foreach (Brand::query()
            ->where('status', 'published')
            ->get(['slug', 'updated_at']) as $brand) {
            $urls[] = [
                'loc' => route('brands.show', ['brand' => $brand->slug]),
                'priority' => '0.6',
                'lastmod' => $brand->updated_at?->toAtomString(),
            ];
        }

        foreach (Campaign::query()
            ->where('status', 'published')
            ->get(['slug', 'public_id', 'updated_at']) as $campaign) {
            $urls[] = [
                'loc' => $campaign->url,
                'priority' => '0.7',
                'lastmod' => $campaign->updated_at?->toAtomString(),
            ];
        }

        foreach (BlogPost::query()
            ->where('status', 'published')
            ->whereNotNull('published_at')
            ->get(['slug', 'published_at', 'updated_at']) as $post) {
            $urls[] = [
                'loc' => route('blog.show', ['post' => $post->slug]),
                'priority' => '0.5',
                'lastmod' => ($post->updated_at ?? $post->published_at)?->toAtomString(),
            ];
        }

        return Response::view('sitemap', ['urls' => $urls], 200)
            ->header('Content-Type', 'application/xml');
    });
})->name('sitemap');

Route::domain((string) config('admin.domain'))
    ->middleware(['auth', 'verified', 'admin.access'])
    ->name('admin.')
    ->group(function () {
        Route::get('pin', [AdminPinChallengeController::class, 'create'])->name('pin.create');
        Route::post('pin', [AdminPinChallengeController::class, 'store'])->name('pin.store');

        Route::middleware('admin.pin')->group(function () {
            Route::get('/', AdminDashboardController::class)->name('dashboard');
            Route::resource('categories', App\Http\Controllers\Admin\CategoryController::class)->except(['show']);

            Route::get('vitrin', [StorefrontManagementController::class, 'index'])->name('storefront.index');
            Route::get('menu', [StorefrontManagementController::class, 'menuIndex'])->name('menu.index');
            Route::get('slider', [StorefrontManagementController::class, 'sliderIndex'])->name('slider.index');
            Route::post('vitrin/menu', [StorefrontManagementController::class, 'storeNavigation'])->name('storefront.navigation.store');
            Route::patch('vitrin/menu/{item:public_id}', [StorefrontManagementController::class, 'updateNavigation'])->name('storefront.navigation.update');
            Route::delete('vitrin/menu/{item:public_id}', [StorefrontManagementController::class, 'destroyNavigation'])->name('storefront.navigation.destroy');
            Route::post('vitrin/banner', [StorefrontManagementController::class, 'storeBanner'])->name('storefront.banners.store');
            Route::patch('vitrin/banner/{banner:public_id}', [StorefrontManagementController::class, 'updateBanner'])->name('storefront.banners.update');
            Route::delete('vitrin/banner/{banner:public_id}', [StorefrontManagementController::class, 'destroyBanner'])->name('storefront.banners.destroy');
            Route::post('vitrin/kampanya', [StorefrontManagementController::class, 'storeCampaign'])->name('storefront.campaigns.store');
            Route::patch('vitrin/kampanya/{campaign:public_id}', [StorefrontManagementController::class, 'updateCampaign'])->name('storefront.campaigns.update');
            Route::delete('vitrin/kampanya/{campaign:public_id}', [StorefrontManagementController::class, 'destroyCampaign'])->name('storefront.campaigns.destroy');

            Route::get('vitrin/popup', [PopupCampaignController::class, 'index'])->name('storefront.popup.index');
            Route::post('vitrin/popup', [PopupCampaignController::class, 'store'])->name('storefront.popup.store');
            Route::post('vitrin/popup/{popupCampaign:public_id}', [PopupCampaignController::class, 'update'])->name('storefront.popup.update');
            Route::delete('vitrin/popup/{popupCampaign:public_id}', [PopupCampaignController::class, 'destroy'])->name('storefront.popup.destroy');

            Route::patch('vitrin/kategori/{category:public_id}', [StorefrontManagementController::class, 'updateCategory'])->name('storefront.categories.update');

            Route::get('stok', [InventoryController::class, 'index'])->name('inventory.index');
            Route::get('stok/urun/yeni', [InventoryController::class, 'createProduct'])->name('inventory.products.create');
            Route::get('stok/urun/{product:public_id}/duzenle', [InventoryController::class, 'editProduct'])->name('inventory.products.edit');
            Route::post('stok/toplu-islem', [InventoryController::class, 'bulkAction'])->name('inventory.bulk-action');
            Route::post('stok/urun', [InventoryController::class, 'storeProduct'])->name('inventory.products.store');
            Route::patch('stok/urun/{product:public_id}', [InventoryController::class, 'updateProduct'])->name('inventory.products.update');
            Route::delete('stok/urun/{product:public_id}', [InventoryController::class, 'destroyProduct'])->name('inventory.products.destroy');
            Route::post('stok/marka', [InventoryController::class, 'storeBrand'])->name('inventory.brands.store');
            Route::post('stok/ai/urun-icerigi', [InventoryController::class, 'generateProductCopy'])
                ->middleware('throttle:10,1')
                ->name('inventory.ai.product-copy');
            Route::post('stok/{product:public_id}/hareket', [InventoryController::class, 'adjust'])->name('inventory.adjust');

            Route::get('siparisler', [OperationsController::class, 'orders'])->name('orders.index');
            Route::get('siparisler/{order:public_id}', [OperationsController::class, 'orderShow'])->name('orders.show');
            Route::patch('siparisler/{order:public_id}/durum', [OperationsController::class, 'updateOrderStatus'])->name('orders.status.update');
            Route::get('siparis-bildirimleri', OrderNotificationController::class)->name('orders.notifications');
            Route::get('kargo', [OperationsController::class, 'shipping'])->name('shipping.index');
            Route::patch('kargo/{order:public_id}', [OperationsController::class, 'updateShipment'])->name('shipping.update');
            Route::get('pazarlama', [OperationsController::class, 'marketing'])->name('marketing.index');
            Route::post('pazarlama/hos-geldin-kuponu', [OperationsController::class, 'updateWelcomeCoupon'])->name('marketing.welcome-coupon.store');
            Route::patch('pazarlama/hos-geldin-kuponu/{coupon:public_id}', [OperationsController::class, 'updateWelcomeCoupon'])->name('marketing.welcome-coupon.update');
            Route::get('yorumlar', [OperationsController::class, 'reviews'])->name('reviews.index');
            Route::patch('yorumlar/{review:public_id}', [OperationsController::class, 'updateReview'])->name('reviews.update');
            Route::delete('yorumlar/{review:public_id}', [OperationsController::class, 'destroyReview'])->name('reviews.destroy');
            Route::get('sayfalar', [OperationsController::class, 'pages'])->name('pages.index');
            Route::post('sayfalar', [OperationsController::class, 'storeBlogPost'])->name('pages.store');
            Route::get('sayfalar/{post:public_id}/duzenle', [OperationsController::class, 'editBlogPost'])->name('pages.edit');
            Route::patch('sayfalar/{post:public_id}', [OperationsController::class, 'updateBlogPost'])->name('pages.update');
            Route::get('kurumsal-sayfalar', [OperationsController::class, 'corporate'])->name('corporate.index');
            Route::get('kurumsal-sayfalar/{page:public_id}/duzenle', [OperationsController::class, 'corporateEdit'])->name('corporate.edit');
            Route::patch('kurumsal-sayfalar/{page:public_id}', [OperationsController::class, 'corporateUpdate'])->name('corporate.update');
            Route::get('magaza-ayarlari', [OperationsController::class, 'settings'])->name('settings.index');
            Route::patch('magaza-ayarlari', [OperationsController::class, 'updateSettings'])->name('settings.update');
            Route::get('kullanicilar', [OperationsController::class, 'users'])->name('users.index');
            Route::patch('kullanicilar/{user}', [OperationsController::class, 'updateUserRole'])->name('users.update');
        });
    });

Route::middleware(['auth', 'verified'])->group(function () {
    Route::redirect('checkout', 'odeme');
    Route::get('odeme', [CheckoutController::class, 'show'])->name('checkout.show');
    Route::post('odeme', [CheckoutController::class, 'store'])->name('checkout.store');
    Route::get('odeme/{order:public_id}/akbank', [AkbankPaymentController::class, 'show'])
        ->middleware('throttle:6,1')
        ->name('payments.akbank.show');
    Route::get('odeme/akbank/{paymentAttempt:idempotency_key}', [AkbankPaymentController::class, 'attempt'])
        ->middleware('throttle:12,1')
        ->name('payments.akbank.attempt');

    Route::redirect('account', 'hesabim')->name('account.redirect');
    Route::get('hesabim', [AccountController::class, 'overview'])->name('account.overview');
    Route::get('hesabim/adreslerim', [AccountController::class, 'addresses'])->name('account.addresses');
    Route::post('hesabim/adreslerim', [AccountController::class, 'storeAddress'])->name('account.addresses.store');
    Route::patch('hesabim/adreslerim/{address}', [AccountController::class, 'updateAddress'])->name('account.addresses.update');
    Route::delete('hesabim/adreslerim/{address}', [AccountController::class, 'destroyAddress'])->name('account.addresses.destroy');
    Route::get('hesabim/bildirimler', [AccountController::class, 'notifications'])->name('account.notifications');
    Route::patch('hesabim/bildirimler/okundu', [AccountController::class, 'markAllNotificationsRead'])->name('account.notifications.read-all');
    Route::patch('hesabim/bildirimler/{notification}/okundu', [AccountController::class, 'markNotificationRead'])->name('account.notifications.read');
    Route::get('hesabim/siparislerim', [AccountController::class, 'orders'])->name('account.orders');
    Route::get('hesabim/siparislerim/{public_id}', [AccountController::class, 'order'])
        ->whereUlid('public_id')
        ->name('account.orders.show');
    // Wishlist (Favorilerim)
    Route::get('hesabim/favorilerim', [AccountController::class, 'wishlist'])->name('account.wishlist');
    Route::post('hesabim/favorilerim/{product:public_id}/toggle', [WishlistController::class, 'toggle'])->name('wishlist.toggle');

    Route::get('hesabim/iade-degisim', [AccountController::class, 'returns'])->name('account.returns');
    Route::post('hesabim/iade-degisim', [AccountController::class, 'storeReturn'])->name('account.returns.store');
    Route::get('fatura/indir/{public_id}', InvoiceDownloadController::class)
        ->whereUlid('public_id')
        ->middleware('signed')
        ->name('invoice.download');
});

require __DIR__.'/settings.php';
