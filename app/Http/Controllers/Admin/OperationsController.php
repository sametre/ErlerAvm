<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\BlogPostRequest;
use App\Http\Requests\Admin\CorporatePageRequest;
use App\Http\Requests\Admin\ReviewModerationRequest;
use App\Http\Requests\Admin\StoreSettingsRequest;
use App\Http\Requests\Admin\UpdateOrderStatusRequest;
use App\Http\Requests\Admin\UpdateShipmentRequest;
use App\Http\Requests\Admin\UpdateUserAccessRequest;
use App\Http\Requests\Admin\WelcomeCouponRequest;
use App\Models\BlogPost;
use App\Models\Campaign;
use App\Models\Category;
use App\Models\CorporatePage;
use App\Models\Coupon;
use App\Models\NewsletterSubscription;
use App\Models\Order;
use App\Models\PaymentAttempt;
use App\Models\Product;
use App\Models\ProductReview;
use App\Models\Segment;
use App\Models\Shipment;
use App\Models\ShipmentMovement;
use App\Models\ShipmentProvider;
use App\Models\StorefrontBanner;
use App\Models\StorefrontNavigationItem;
use App\Models\StoreSetting;
use App\Models\User;
use App\Notifications\OrderStatusUpdated;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Inertia\Inertia;
use Inertia\Response;
use Spatie\Permission\Models\Role;

class OperationsController extends Controller
{
    public function orders(Request $request): Response
    {
        $search = $request->query('q');

        return Inertia::render('Admin/Orders', [
            'filters' => [
                'q' => $search,
            ],
            'metrics' => [
                ['label' => 'Toplam sipariş', 'value' => Order::query()->count()],
                ['label' => 'Ödeme bekleyen', 'value' => Order::query()->where('payment_status', 'pending')->count()],
                ['label' => 'Ödenmiş', 'value' => Order::query()->where('payment_status', 'paid')->count()],
                ['label' => 'Ödeme denemeleri', 'value' => PaymentAttempt::query()->count()],
            ],
            'orders' => Order::query()
                ->withCount('items')
                ->when($search, function ($query, $search) {
                    $query->where('order_number', 'like', "%{$search}%")
                        ->orWhere('email', 'like', "%{$search}%")
                        ->orWhere('billing_address', 'like', "%{$search}%");
                })
                ->latest()
                ->paginate(25)
                ->through(fn (Order $order) => [
                    'public_id' => $order->public_id,
                    'order_number' => $order->order_number,
                    'email' => $order->email,
                    'status' => $order->status,
                    'status_label' => $order->status_label,
                    'payment_status' => $order->payment_status,
                    'payment_status_label' => $order->payment_status_label,
                    'grand_total' => number_format((float) $order->grand_total, 2, ',', '.').' ₺',
                    'currency' => $order->currency,
                    'items_count' => $order->items_count,
                    'created_at' => $order->created_at?->toDateTimeString(),
                    'url' => route('admin.orders.show', ['order' => $order->public_id], false),
                ]),
        ]);
    }

    public function orderShow(Order $order): Response
    {
        $this->ensureDefaultShipmentProviders();

        $order->load([
            'items.product:id,public_id,name,slug',
            'user:id,name,email',
            'shipment.provider',
        ]);

        return Inertia::render('Admin/OrderShow', [
            'order' => [
                'public_id' => $order->public_id,
                'order_number' => $order->order_number,
                'email' => $order->email,
                'status' => $order->status,
                'status_label' => $order->status_label,
                'payment_status' => $order->payment_status,
                'payment_status_label' => $order->payment_status_label,
                'subtotal' => number_format((float) $order->subtotal, 2, ',', '.').' ₺',
                'shipping_total' => number_format((float) $order->shipping_total, 2, ',', '.').' ₺',
                'discount_total' => number_format((float) $order->discount_total, 2, ',', '.').' ₺',
                'tax_total' => number_format((float) $order->tax_total, 2, ',', '.').' ₺',
                'grand_total' => number_format((float) $order->grand_total, 2, ',', '.').' ₺',
                'currency' => $order->currency,
                'billing_address' => $order->billing_address,
                'shipping_address' => $order->shipping_address,
                'created_at' => $order->created_at?->toDateTimeString(),
                'updated_at' => $order->updated_at?->toDateTimeString(),
                'customer' => $order->user?->only(['name', 'email']),
                'shipment' => $this->shipmentPayload($order->shipment),
                'items' => $order->items->map(fn ($item) => [
                    'public_id' => $item->public_id,
                    'product_public_id' => $item->product_public_id,
                    'name' => $item->name,
                    'sku' => $item->sku,
                    'quantity' => $item->quantity,
                    'unit_price' => number_format((float) $item->unit_price, 2, ',', '.').' ₺',
                    'line_total' => number_format((float) $item->line_total, 2, ',', '.').' ₺',
                    'snapshot' => $item->snapshot,
                ]),
            ],
            'paymentAttempts' => PaymentAttempt::query()
                ->where('order_id', $order->id)
                ->latest()
                ->get(['provider', 'merchant_oid', 'status', 'payment_amount', 'total_amount', 'currency', 'failed_reason_code', 'failed_reason_msg', 'created_at', 'completed_at'])
                ->map(fn (PaymentAttempt $attempt) => [
                    'provider' => $attempt->provider,
                    'merchant_oid' => $attempt->merchant_oid,
                    'status' => $attempt->status,
                    'payment_amount' => number_format((float) $attempt->payment_amount, 2, ',', '.').' ₺',
                    'total_amount' => number_format((float) $attempt->total_amount, 2, ',', '.').' ₺',
                    'currency' => $attempt->currency,
                    'failed_reason_code' => $attempt->failed_reason_code,
                    'failed_reason_msg' => $attempt->failed_reason_msg,
                    'created_at' => $attempt->created_at?->toDateTimeString(),
                    'completed_at' => $attempt->completed_at?->toDateTimeString(),
                ]),
            'shipmentProviders' => $this->shipmentProvidersPayload(),
        ]);
    }

    public function updateOrderStatus(UpdateOrderStatusRequest $request, Order $order): RedirectResponse
    {
        $before = $order->only(['status', 'payment_status']);
        $order->update($request->safe()->only(['status', 'payment_status']));

        $statusLabels = [
            'payment_pending' => 'Ödeme bekleniyor',
            'processing' => 'Hazırlanıyor',
            'shipped' => 'Kargolandı',
            'completed' => 'Tamamlandı',
            'cancelled' => 'İptal edildi',
        ];

        $newStatus = $request->input('status');
        $label = $statusLabels[$newStatus] ?? $newStatus;

        if ($before['status'] !== $newStatus && $order->user) {
            $order->user->notify(new OrderStatusUpdated($order, $label));
        }

        logger()->info('Admin order status updated.', [
            'order_id' => $order->id,
            'order_number' => $order->order_number,
            'admin_id' => $request->user()?->id,
            'before' => $before,
            'after' => $order->only(['status', 'payment_status']),
            'note' => $request->input('admin_note'),
        ]);

        return back()->with('toast', [
            'type' => 'success',
            'message' => "Sipariş durumu '{$label}' olarak güncellendi.",
        ]);
    }

    public function reviews(): Response
    {
        return Inertia::render('Admin/Reviews', [
            'reviews' => ProductReview::query()
                ->with(['product:id,public_id,name,slug', 'user:id,name,email'])
                ->latest()
                ->paginate(25)
                ->through(fn (ProductReview $review) => [
                    'public_id' => $review->public_id,
                    'rating' => $review->rating,
                    'comment' => $review->comment,
                    'admin_reply' => $review->admin_reply,
                    'status' => $review->status,
                    'answered_at' => $review->answered_at?->toDateTimeString(),
                    'created_at' => $review->created_at?->toDateTimeString(),
                    'product' => [
                        'name' => $review->product?->name,
                        'public_id' => $review->product?->public_id,
                    ],
                    'user' => [
                        'name' => $review->user?->name,
                        'email' => $review->user?->email,
                    ],
                ]),
            'metrics' => [
                ['label' => 'Bekleyen', 'value' => ProductReview::query()->where('status', 'pending')->count()],
                ['label' => 'Onaylı', 'value' => ProductReview::query()->where('status', 'approved')->count()],
                ['label' => 'Reddedilen', 'value' => ProductReview::query()->where('status', 'rejected')->count()],
            ],
        ]);
    }

    public function updateReview(ReviewModerationRequest $request, ProductReview $review): RedirectResponse
    {
        $data = $request->validated();
        $hasReplyInput = array_key_exists('admin_reply', $data);
        $reply = Str::of((string) ($data['admin_reply'] ?? ''))->squish()->toString();

        $review->status = $data['status'];

        if ($hasReplyInput) {
            $review->admin_reply = $reply !== '' ? $reply : null;
        }

        if ($hasReplyInput && $reply !== '') {
            $review->answered_at = now();
            $review->answered_by_id = $request->user()?->id;
        }

        $review->save();

        logger()->info('Product review moderated.', [
            'review_id' => $review->id,
            'status' => $review->status,
            'answered' => $reply !== '',
            'admin_id' => $request->user()?->id,
        ]);

        return back()->with('toast', [
            'type' => 'success',
            'message' => $reply !== '' ? 'Yorum cevaplandı ve durumu güncellendi.' : 'Yorum durumu güncellendi.',
        ]);
    }

    public function destroyReview(Request $request, ProductReview $review): RedirectResponse
    {
        $review->delete();

        logger()->warning('Product review deleted by admin.', [
            'review_id' => $review->id,
            'admin_id' => $request->user()?->id,
        ]);

        return back()->with('toast', [
            'type' => 'success',
            'message' => 'Yorum silindi.',
        ]);
    }

    public function shipping(Request $request): Response
    {
        $this->ensureDefaultShipmentProviders();

        $search = $request->query('q');

        return Inertia::render('Admin/Shipping', [
            'filters' => [
                'q' => $search,
            ],
            'metrics' => [
                ['label' => 'Ödenmiş sipariş', 'value' => Order::query()->where('payment_status', 'paid')->count()],
                ['label' => 'Hazırlanacak', 'value' => Order::query()->where('payment_status', 'paid')->where('status', 'processing')->count()],
                ['label' => 'Kargoda', 'value' => Order::query()->where('status', 'shipped')->count()],
                ['label' => 'Teslim edilen', 'value' => Order::query()->where('status', 'completed')->count()],
            ],
            'orders' => Order::query()
                ->with(['shipment.provider'])
                ->where('payment_status', 'paid')
                ->whereIn('status', ['processing', 'shipped', 'completed'])
                ->when($search, function ($query, $search) {
                    $query->where(function ($query) use ($search): void {
                        $query->where('order_number', 'like', "%{$search}%")
                            ->orWhere('email', 'like', "%{$search}%");
                    });
                })
                ->latest()
                ->paginate(25)
                ->through(fn (Order $order) => [
                    'public_id' => $order->public_id,
                    'order_number' => $order->order_number,
                    'email' => $order->email,
                    'status' => $order->status,
                    'status_label' => $order->status_label,
                    'payment_status' => $order->payment_status,
                    'shipping_address' => $order->shipping_address,
                    'shipment' => $this->shipmentPayload($order->shipment),
                    'created_at' => $order->created_at?->toDateTimeString(),
                ]),
            'shipmentProviders' => $this->shipmentProvidersPayload(),
        ]);
    }

    public function updateShipment(UpdateShipmentRequest $request, Order $order): RedirectResponse
    {
        $data = $request->validated();
        $provider = ShipmentProvider::query()->whereKey($data['shipment_provider_id'])->firstOrFail();

        DB::transaction(function () use ($data, $provider, $order, $request): void {
            $shipment = Shipment::query()->updateOrCreate(
                ['order_id' => $order->id],
                [
                    'shipment_provider_id' => $provider->id,
                    'status' => $data['status'],
                    'tracking_number' => $data['tracking_number'],
                    'tracking_url' => $provider->trackingUrl($data['tracking_number']),
                    'admin_note' => $data['admin_note'] ?? null,
                    'created_by_id' => $request->user()?->id,
                    'customer_notified_at' => ($data['notify_customer'] ?? false) ? now() : $order->shipment?->customer_notified_at,
                    'shipped_at' => $data['status'] === 'shipped' ? ($order->shipment?->shipped_at ?? now()) : $order->shipment?->shipped_at,
                    'delivered_at' => $data['status'] === 'delivered' ? ($order->shipment?->delivered_at ?? now()) : $order->shipment?->delivered_at,
                ],
            );

            ShipmentMovement::query()->create([
                'shipment_id' => $shipment->id,
                'user_id' => $request->user()?->id,
                'status' => $data['status'],
                'note' => $data['admin_note'] ?? null,
                'payload' => [
                    'provider' => $provider->name,
                    'tracking_number' => $data['tracking_number'],
                    'customer_notified' => (bool) ($data['notify_customer'] ?? false),
                ],
            ]);

            if ($data['status'] === 'shipped' && $order->status !== 'shipped') {
                $order->update(['status' => 'shipped']);
            }

            if ($data['status'] === 'delivered' && $order->status !== 'completed') {
                $order->update(['status' => 'completed']);
            }
        });

        logger()->info('Admin shipment updated.', [
            'order_id' => $order->id,
            'order_number' => $order->order_number,
            'admin_id' => $request->user()?->id,
            'provider_id' => $provider->id,
            'tracking_number' => $data['tracking_number'],
            'status' => $data['status'],
            'notify_customer' => (bool) ($data['notify_customer'] ?? false),
        ]);

        return back()->with('toast', [
            'type' => 'success',
            'message' => ($data['notify_customer'] ?? false)
                ? 'Kargo bilgisi kaydedildi ve müşteri hesabında gösterilecek.'
                : 'Kargo bilgisi kaydedildi.',
        ]);
    }

    public function marketing(): Response
    {
        $settings = StoreSetting::publicPayload();

        return Inertia::render('Admin/Marketing', [
            'metrics' => [
                ['label' => 'Kampanyalar', 'value' => Campaign::query()->count()],
                ['label' => 'Kuponlar', 'value' => Coupon::query()->count()],
                ['label' => 'Segmentler', 'value' => Segment::query()->count()],
                ['label' => 'Yayındaki banner', 'value' => StorefrontBanner::query()->where('status', 'published')->count()],
                ['label' => 'Bülten aboneleri', 'value' => NewsletterSubscription::query()->count()],
            ],
            'campaigns' => Campaign::query()
                ->latest()
                ->limit(20)
                ->get(['public_id', 'name', 'slug', 'status', 'starts_at', 'ends_at']),
            'coupons' => Coupon::query()
                ->latest()
                ->limit(20)
                ->get(['public_id', 'code', 'description', 'type', 'value', 'status', 'usage_limit', 'used_count', 'starts_at', 'ends_at']),
            'welcomeCoupon' => Coupon::query()
                ->where(function ($query): void {
                    $query->where('code', 'like', 'HOSGELDIN%')
                        ->orWhere('code', 'like', 'WELCOME%');
                })
                ->latest()
                ->first(['public_id', 'code', 'description', 'type', 'value', 'status', 'usage_limit', 'used_count', 'starts_at', 'ends_at']),
            'segments' => Segment::query()
                ->latest()
                ->limit(20)
                ->get(['public_id', 'name', 'description', 'status']),
            'socialSettings' => [
                'facebook_url' => $settings['facebook_url'] ?? '',
                'instagram_url' => $settings['instagram_url'] ?? '',
                'twitter_url' => $settings['twitter_url'] ?? '',
                'youtube_url' => $settings['youtube_url'] ?? '',
            ],
            'storeSettings' => $settings,
        ]);
    }

    public function updateWelcomeCoupon(WelcomeCouponRequest $request, ?Coupon $coupon = null): RedirectResponse
    {
        $data = $request->validated();

        $coupon ??= new Coupon;
        $coupon->fill($data);
        $coupon->save();

        logger()->info('Admin welcome coupon saved.', [
            'coupon_id' => $coupon->id,
            'admin_id' => $request->user()?->id,
            'status' => $coupon->status,
        ]);

        return back()->with('toast', [
            'type' => 'success',
            'message' => 'Hoş geldin kuponu kaydedildi.',
        ]);
    }

    public function pages(Request $request): Response
    {
        $search = Str::of((string) $request->query('q', ''))->squish()->limit(100, '')->toString();

        return Inertia::render('Admin/Pages', [
            'filters' => ['q' => $search],
            'metrics' => [
                ['label' => 'Blog yazıları', 'value' => BlogPost::query()->count()],
                ['label' => 'Yayındaki yazı', 'value' => BlogPost::query()->where('status', 'published')->count()],
                ['label' => 'Taslak', 'value' => BlogPost::query()->where('status', 'draft')->count()],
                ['label' => 'Kategoriler', 'value' => Category::query()->count()],
            ],
            'posts' => BlogPost::query()
                ->when($search !== '', function ($query) use ($search): void {
                    $query->where(function ($query) use ($search): void {
                        $query->where('title', 'like', "%{$search}%")
                            ->orWhere('slug', 'like', "%{$search}%");
                    });
                })
                ->latest()
                ->paginate(20)
                ->withQueryString()
                ->through(fn (BlogPost $post) => [
                    'public_id' => $post->public_id,
                    'title' => $post->title,
                    'slug' => $post->slug,
                    'status' => $post->status,
                    'excerpt' => $post->excerpt,
                    'cover_image_path' => $post->cover_image_path,
                    'seo_title' => $post->seo_title,
                    'seo_description' => $post->seo_description,
                    'published_at' => $post->published_at?->toDateTimeString(),
                    'updated_at' => $post->updated_at?->toDateTimeString(),
                    'url' => route('blog.show', ['post' => $post->slug], false),
                    'edit_url' => route('admin.pages.edit', ['post' => $post->public_id], false),
                ]),
        ]);
    }

    public function storeBlogPost(BlogPostRequest $request): RedirectResponse
    {
        $post = BlogPost::query()->create($this->blogPostData($request));

        logger()->info('Admin blog post created.', [
            'blog_post_id' => $post->id,
            'admin_id' => $request->user()?->id,
        ]);

        return redirect()
            ->route('admin.pages.edit', ['post' => $post->public_id])
            ->with('toast', [
                'type' => 'success',
                'message' => 'Blog yazısı oluşturuldu.',
            ]);
    }

    public function editBlogPost(BlogPost $post): Response
    {
        return Inertia::render('Admin/PageEdit', [
            'post' => [
                'public_id' => $post->public_id,
                'title' => $post->title,
                'slug' => $post->slug,
                'status' => $post->status,
                'excerpt' => $post->excerpt,
                'content' => $post->content,
                'cover_image_path' => $post->cover_image_path,
                'seo_title' => $post->seo_title,
                'seo_description' => $post->seo_description,
                'published_at' => $post->published_at?->toDateTimeString(),
            ],
        ]);
    }

    public function updateBlogPost(BlogPostRequest $request, BlogPost $post): RedirectResponse
    {
        $post->update($this->blogPostData($request, $post));

        logger()->info('Admin blog post updated.', [
            'blog_post_id' => $post->id,
            'admin_id' => $request->user()?->id,
            'status' => $post->status,
        ]);

        return back()->with('toast', [
            'type' => 'success',
            'message' => 'Blog yazısı güncellendi.',
        ]);
    }

    public function corporate(): Response
    {
        $pages = collect([
            'iletisim' => 'İletişim (Üst Metin)',
            'kargo-hesaplama' => 'Kargo Hesaplama (Üst Metin)',
            'sikca-sorulan-sorular' => 'Sıkça Sorulan Sorular',
            'kurumsal' => 'Kurumsal',
            'hakkimizda' => 'Hakkımızda',
            'vizyon-misyon' => 'Vizyon & Misyon',
            'magazalarimiz' => 'Mağazalarımız',
            'kariyer-ve-is-basvurusu' => 'Kariyer ve İş Başvurusu',
            'banka-hesaplarimiz' => 'Banka Hesaplarımız',
            'teslimat-ve-kargo' => 'Teslimat ve Kargo',
            'guvenli-alisveris' => 'Güvenli Alışveriş',
            'satis-kosullari' => 'Satış Koşulları',
            'iade-degisim-politikasi' => 'İade Değişim Politikası',
            'mesafeli-satis-sozlesmesi' => 'Mesafeli Satış Sözleşmesi',
            'on-bilgilendirme-formu' => 'Ön Bilgilendirme Formu',
            'gizlilik-politikasi' => 'Gizlilik Politikası',
            'kvkk-aydinlatma-metni' => 'KVKK Aydınlatma Metni',
            'cerez-politikasi' => 'Çerez Politikası',
            'uyelik-sozlesmesi' => 'Üyelik Sözleşmesi',
            'odeme-secenekleri' => 'Ödeme Seçenekleri',
        ]);

        foreach ($pages as $slug => $title) {
            CorporatePage::firstOrCreate(
                ['slug' => $slug],
                ['title' => $title, 'content' => '', 'status' => 'published', 'is_active' => true]
            );
        }

        return Inertia::render('Admin/CorporatePages', [
            'pages' => CorporatePage::query()
                ->orderBy('title')
                ->get()
                ->map(fn ($page) => [
                    'public_id' => $page->public_id,
                    'title' => $page->title,
                    'slug' => $page->slug,
                    'status' => $page->status,
                    'is_active' => $page->is_active,
                    'url' => route('legal.show', ['page' => $page->slug], false),
                    'edit_url' => route('admin.corporate.edit', ['page' => $page->public_id], false),
                ]),
        ]);
    }

    public function corporateEdit(CorporatePage $page): Response
    {
        return Inertia::render('Admin/CorporatePageEdit', [
            'page' => [
                'public_id' => $page->public_id,
                'title' => $page->title,
                'slug' => $page->slug,
                'content' => $page->content,
                'seo_meta' => $page->seo_meta,
                'status' => $page->status,
                'is_active' => $page->is_active,
            ],
        ]);
    }

    public function corporateUpdate(CorporatePageRequest $request, CorporatePage $page): RedirectResponse
    {
        $validated = $request->validated();
        $validated['content'] = $this->sanitizeAdminHtml((string) ($validated['content'] ?? ''));

        $page->update($validated);

        logger()->info('Corporate page updated by admin.', [
            'corporate_page_id' => $page->id,
            'admin_id' => $request->user()?->id,
            'status' => $page->status,
            'is_active' => $page->is_active,
        ]);

        return back()->with('toast', [
            'type' => 'success',
            'message' => 'Kurumsal sayfa başarıyla güncellendi.',
        ]);
    }

    public function settings(): Response
    {
        return Inertia::render('Admin/StoreSettings', [
            'settings' => StoreSetting::publicPayload(),
            'metrics' => [
                ['label' => 'Ürün', 'value' => Product::query()->count()],
                ['label' => 'Menü kaydı', 'value' => StorefrontNavigationItem::query()->count()],
                ['label' => 'Banner', 'value' => StorefrontBanner::query()->count()],
                ['label' => 'Yayındaki blog', 'value' => BlogPost::query()->where('status', 'published')->count()],
            ],
            'availableSections' => [
                ['key' => 'hero_slider', 'label' => 'Ana slider'],
                ['key' => 'featured_categories', 'label' => 'Kategori vitrinleri'],
                ['key' => 'campaigns', 'label' => 'Kampanya alanları'],
                ['key' => 'best_sellers', 'label' => 'Çok satan ürünler'],
                ['key' => 'new_products', 'label' => 'Yeni ürünler'],
                ['key' => 'blog', 'label' => 'Blog ve içerik'],
                ['key' => 'newsletter', 'label' => 'Bülten aboneliği'],
            ],
        ]);
    }

    public function updateSettings(StoreSettingsRequest $request): RedirectResponse
    {
        $data = $request->validated();
        $publicKeys = [
            'store_name',
            'logo_url',
            'favicon_url',
            'primary_color',
            'accent_color',
            'contact_phone',
            'contact_email',
            'address',
            'facebook_url',
            'instagram_url',
            'twitter_url',
            'youtube_url',
            'homepage_sections',
            'maintenance_mode_active',
        ];

        DB::transaction(function () use ($data, $publicKeys, $request): void {
            foreach ($publicKeys as $key) {
                StoreSetting::setValue(
                    $key,
                    $data[$key] ?? null,
                    in_array($key, ['facebook_url', 'instagram_url', 'twitter_url', 'youtube_url'], true) ? 'social' : 'storefront',
                    true,
                    $request->user()?->id,
                );
            }
        });

        logger()->info('Store settings updated by admin.', [
            'admin_id' => $request->user()?->id,
            'keys' => $publicKeys,
        ]);

        return back()->with('toast', [
            'type' => 'success',
            'message' => 'Mağaza ayarları kaydedildi.',
        ]);
    }

    public function users(): Response
    {
        return Inertia::render('Admin/Users', [
            'metrics' => [
                ['label' => 'Toplam kullanıcı', 'value' => User::query()->count()],
                ['label' => 'Admin', 'value' => User::query()->where('is_admin', true)->count()],
                ['label' => 'Müşteri', 'value' => User::query()->where('is_admin', false)->count()],
            ],
            'users' => User::query()
                ->with('roles')
                ->latest()
                ->paginate(25)
                ->through(fn (User $user) => [
                    'id' => $user->id,
                    'name' => $user->name,
                    'email' => $user->email,
                    'is_admin' => $user->is_admin,
                    'roles' => $user->roles->pluck('name'),
                    'created_at' => $user->created_at?->toDateTimeString(),
                ]),
            'availableRoles' => Role::pluck('name'),
        ]);
    }

    public function updateUserRole(UpdateUserAccessRequest $request, User $user): RedirectResponse
    {
        $validated = $request->validated();

        if (array_key_exists('is_admin', $validated)) {
            $user->is_admin = (bool) $validated['is_admin'];
        }

        if (! empty($validated['admin_pin'])) {
            $user->admin_pin_hash = Hash::make((string) $validated['admin_pin']);
            $user->admin_2fa_verified_at = null;
        }

        if ($user->isDirty()) {
            $user->save();
        }

        if (array_key_exists('roles', $validated)) {
            $user->syncRoles($validated['roles']);
        }

        return back()->with('toast', [
            'type' => 'success',
            'message' => 'Kullanıcı yetkileri güncellendi.',
        ]);
    }

    public function roles(Request $request): Response
    {
        return $this->section('Roller', 'Admin panel rol ve yetki matrisi.', [], [
            'columns' => ['Bilgi'],
            'rows' => collect([['Takım sistemi ve roller kaldırıldı.']]),
            'notice' => 'Roller özelliği devre dışı.',
        ]);
    }

    private function ensureDefaultShipmentProviders(): void
    {
        $providers = [
            [
                'name' => 'Aras Kargo',
                'code' => 'aras',
                'tracking_url_template' => 'https://kargotakip.araskargo.com.tr/mainpage.aspx?code={tracking_number}',
                'sort_order' => 10,
            ],
            [
                'name' => 'MNG Kargo',
                'code' => 'mng',
                'tracking_url_template' => 'https://www.mngkargo.com.tr/gonderi-takip?code={tracking_number}',
                'sort_order' => 20,
            ],
            [
                'name' => 'Yurtiçi Kargo',
                'code' => 'yurtici',
                'tracking_url_template' => 'https://www.yurticikargo.com/tr/online-servisler/gonderi-sorgula?code={tracking_number}',
                'sort_order' => 30,
            ],
            [
                'name' => 'Sürat Kargo',
                'code' => 'surat',
                'tracking_url_template' => 'https://www.suratkargo.com.tr/KargoTakip/?kargotakipno={tracking_number}',
                'sort_order' => 40,
            ],
        ];

        foreach ($providers as $provider) {
            ShipmentProvider::query()->firstOrCreate(
                ['code' => $provider['code']],
                [
                    ...$provider,
                    'status' => 'active',
                    'integration_mode' => 'manual',
                ],
            );
        }
    }

    /**
     * @return array<int, array<string, mixed>>
     */
    private function shipmentProvidersPayload(): array
    {
        return ShipmentProvider::query()
            ->where('status', 'active')
            ->orderBy('sort_order')
            ->orderBy('name')
            ->get(['id', 'public_id', 'name', 'code', 'tracking_url_template', 'integration_mode'])
            ->map(fn (ShipmentProvider $provider) => [
                'id' => $provider->id,
                'public_id' => $provider->public_id,
                'name' => $provider->name,
                'code' => $provider->code,
                'tracking_url_template' => $provider->tracking_url_template,
                'integration_mode' => $provider->integration_mode,
            ])
            ->all();
    }

    /**
     * @return array<string, mixed>|null
     */
    private function shipmentPayload(?Shipment $shipment): ?array
    {
        if (! $shipment) {
            return null;
        }

        return [
            'public_id' => $shipment->public_id,
            'status' => $shipment->status,
            'status_label' => $shipment->status_label,
            'shipment_provider_id' => $shipment->shipment_provider_id,
            'provider_name' => $shipment->provider?->name,
            'tracking_number' => $shipment->tracking_number,
            'tracking_url' => $shipment->tracking_url,
            'customer_notified_at' => $shipment->customer_notified_at?->toDateTimeString(),
            'shipped_at' => $shipment->shipped_at?->toDateTimeString(),
            'delivered_at' => $shipment->delivered_at?->toDateTimeString(),
            'admin_note' => $shipment->admin_note,
        ];
    }

    /**
     * @return array<string, mixed>
     */
    private function blogPostData(BlogPostRequest $request, ?BlogPost $post = null): array
    {
        $data = $request->validated();
        $status = $data['status'];
        $slug = Str::of((string) ($data['slug'] ?? ''))->slug()->toString();

        if ($slug === '') {
            $slug = Str::slug((string) $data['title']);
        }

        if ($request->hasFile('cover_image')) {
            $path = $request->file('cover_image')?->store('blog', 'public');

            if ($path) {
                $data['cover_image_path'] = Storage::url($path);
            }
        }

        unset($data['cover_image']);

        return [
            ...$data,
            'slug' => $this->uniqueBlogSlug($slug, $post),
            'content' => $this->sanitizeAdminHtml((string) ($data['content'] ?? '')),
            'seo_title' => Str::of((string) ($data['seo_title'] ?? $data['title']))->squish()->limit(180, '')->toString(),
            'seo_description' => Str::of((string) ($data['seo_description'] ?? $data['excerpt'] ?? ''))->stripTags()->squish()->limit(320, '')->toString(),
            'published_at' => $status === 'published' ? ($post?->published_at ?? now()) : null,
            'approved_by_id' => $status === 'published' ? $request->user()?->id : null,
        ];
    }

    private function uniqueBlogSlug(string $slug, ?BlogPost $post = null): string
    {
        $base = $slug !== '' ? $slug : Str::slug('blog-yazisi');
        $candidate = $base;
        $counter = 2;

        while (BlogPost::query()
            ->where('slug', $candidate)
            ->when($post, fn ($query) => $query->whereKeyNot($post->id))
            ->exists()) {
            $candidate = $base.'-'.$counter;
            $counter++;
        }

        return $candidate;
    }

    private function sanitizeAdminHtml(string $html): string
    {
        $allowedTags = '<p><br><strong><b><em><i><u><ul><ol><li><h2><h3><h4><blockquote><a><span>';
        $clean = strip_tags($html, $allowedTags);
        $clean = preg_replace('/\s+on[a-z]+\s*=\s*("[^"]*"|\'[^\']*\'|[^\s>]+)/i', '', (string) $clean);
        $clean = preg_replace('/javascript\s*:/i', '', (string) $clean);

        return trim((string) $clean);
    }

    /**
     * @param  array<int, array{label: string, value: int}>  $metrics
     * @param  array{columns: array<int, string>, rows: mixed, notice: string}  $table
     */
    private function section(string $title, string $description, array $metrics, array $table): Response
    {
        return Inertia::render('Admin/Section', [
            'title' => $title,
            'description' => $description,
            'metrics' => $metrics,
            'table' => $table,
        ]);
    }
}
