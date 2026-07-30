<?php

namespace App\Http\Controllers\Account;

use App\Http\Controllers\Controller;
use App\Http\Requests\CustomerAddressRequest;
use App\Models\CustomerAddress;
use App\Models\Order;
use App\Models\OrderReturn;
use App\Models\User;
use App\Models\UserNotification;
use App\Services\Api\AddressBookService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class AccountController extends Controller
{
    public function __construct(private readonly AddressBookService $addresses) {}

    public function overview(): Response
    {
        $user = $this->currentUser();

        return Inertia::render('Account/Overview', [
            'unreadNotifications' => $user->notifications()->unread()->count(),
        ]);
    }

    public function addresses(): Response
    {
        $user = $this->currentUser();

        return Inertia::render('Account/Addresses', [
            'addresses' => $this->addresses->listForUser($user)
                ->map(fn (CustomerAddress $address) => [
                    'public_id' => $address->public_id,
                    'type' => $address->type,
                    'title' => $address->title,
                    'full_name' => $address->full_name,
                    'phone' => $address->phone,
                    'city' => $address->city,
                    'district' => $address->district,
                    'postal_code' => $address->postal_code,
                    'address_line' => $address->address_line,
                    'is_default' => $address->is_default,
                ]),
        ]);
    }

    public function storeAddress(CustomerAddressRequest $request): RedirectResponse
    {
        $this->addresses->create($this->currentUser(), $request->validated());

        return back()->with('success', 'Adres eklendi.');
    }

    public function updateAddress(CustomerAddressRequest $request, CustomerAddress $address): RedirectResponse
    {
        $user = $this->currentUser();

        abort_unless($address->user_id === $user->id, 404);

        $this->addresses->update($user, $address, $request->validated());

        return back()->with('success', 'Adres güncellendi.');
    }

    public function destroyAddress(CustomerAddress $address): RedirectResponse
    {
        $user = $this->currentUser();

        abort_unless($address->user_id === $user->id, 404);

        $this->addresses->delete($user, $address);

        return back()->with('success', 'Adres silindi.');
    }

    public function notifications(): Response
    {
        $user = $this->currentUser();

        return Inertia::render('Account/Notifications', [
            'notifications' => $user
                ->notifications()
                ->latest()
                ->limit(50)
                ->get()
                ->map(fn (UserNotification $notification) => [
                    'public_id' => $notification->public_id,
                    'type' => $notification->type,
                    'title' => $notification->title,
                    'body' => $notification->body,
                    'action_url' => $notification->action_url,
                    'read_at' => $notification->read_at?->toDateTimeString(),
                    'created_at' => $notification->created_at?->diffForHumans(),
                ]),
            'unreadCount' => $user->notifications()->unread()->count(),
        ]);
    }

    public function markNotificationRead(UserNotification $notification): RedirectResponse
    {
        abort_unless($notification->user_id === $this->currentUser()->id, 404);

        $notification->forceFill(['read_at' => $notification->read_at ?? now()])->save();

        return back()->with('success', 'Bildirim okundu.');
    }

    public function markAllNotificationsRead(): RedirectResponse
    {
        $this->currentUser()
            ->notifications()
            ->unread()
            ->update(['read_at' => now()]);

        return back()->with('success', 'Tüm bildirimler okundu.');
    }

    public function orders(): Response
    {
        return Inertia::render('Account/Orders', [
            'orders' => Order::query()
                ->with(['items' => function ($q) {
                    $q->select('id', 'order_id', 'product_id', 'name', 'quantity');
                }, 'items.product' => function ($q) {
                    $q->select('id', 'public_id');
                }])
                ->where('user_id', auth()->id())
                ->latest()
                ->paginate(15)
                ->through(fn ($order) => [
                    ...$order->only(['public_id', 'order_number', 'status', 'payment_status', 'grand_total', 'currency', 'created_at']),
                    'items' => $order->items->map(fn ($item) => [
                        'name' => $item->name,
                        'quantity' => $item->quantity,
                        'product_url' => $item->product ? '/urun/'.$item->product->public_id : null,
                    ]),
                ]),
        ]);
    }

    public function order(string $publicId): Response
    {
        $order = Order::query()
            ->with([
                'items:id,order_id,product_id,name,sku,quantity,unit_price,line_total',
                'items.product:id,public_id',
                'shipment.provider',
            ])
            ->where('user_id', auth()->id())
            ->where('public_id', $publicId)
            ->firstOrFail();

        $paymentUrl = in_array($order->payment_status, ['payment_pending', 'pending', 'failed'], true)
            ? route('payments.akbank.show', ['order' => $order->public_id])
            : null;

        return Inertia::render('Account/OrderShow', [
            'order' => [
                ...$order->only(['public_id', 'order_number', 'status', 'payment_status', 'subtotal', 'shipping_total', 'discount_total', 'tax_total', 'grand_total', 'currency', 'created_at']),
                'items' => $order->items->map(fn ($item) => [
                    ...$item->only(['name', 'sku', 'quantity', 'unit_price', 'line_total']),
                    'product_url' => $item->product ? '/urun/'.$item->product->public_id : null,
                ]),
                'shipment' => $order->shipment ? [
                    'status' => $order->shipment->status,
                    'status_label' => $order->shipment->status_label,
                    'provider_name' => $order->shipment->provider?->name,
                    'tracking_number' => $order->shipment->tracking_number,
                    'tracking_url' => $order->shipment->tracking_url,
                    'customer_notified_at' => $order->shipment->customer_notified_at?->toDateTimeString(),
                    'shipped_at' => $order->shipment->shipped_at?->toDateTimeString(),
                    'delivered_at' => $order->shipment->delivered_at?->toDateTimeString(),
                ] : null,
            ],
            'paymentUrl' => $paymentUrl,
        ]);
    }

    public function wishlist(Request $request): Response
    {
        $wishlists = $request->user()->wishlistItems()->with('product')->latest()->get();

        $wishlistProducts = $wishlists->map(function ($w) {
            $product = $w->product;

            return [
                'name' => $product->name,
                'public_id' => $product->public_id,
                'price' => $product->price,
                'compare_at_price' => $product->compare_at_price,
                'stock_quantity' => $product->stock_quantity,
                'images' => $product->images,
                'url' => route('products.show', $product->slug, false),
                'brand' => $product->brand ? [
                    'name' => $product->brand->name,
                    'slug' => $product->brand->slug,
                ] : null,
            ];
        });

        return Inertia::render('Account/Wishlist', [
            'wishlistProducts' => $wishlistProducts,
        ]);
    }

    public function returns(): Response
    {
        $user = $this->currentUser();

        $returns = OrderReturn::with(['order', 'items.orderItem'])
            ->where('user_id', $user->id)
            ->latest()
            ->get()
            ->map(fn ($ret) => [
                'public_id' => $ret->public_id,
                'order_number' => $ret->order->order_number,
                'status' => $ret->status,
                'shipping_code' => $ret->shipping_code,
                'customer_note' => $ret->customer_note,
                'created_at' => $ret->created_at->toDateTimeString(),
                'items' => $ret->items->map(fn ($item) => [
                    'name' => $item->orderItem->name,
                    'quantity' => $item->quantity,
                    'reason' => $item->reason,
                ]),
            ]);

        // Get delivered orders from the last 30 days
        $eligibleOrders = Order::with('items.product')
            ->where('user_id', $user->id)
            ->where('status', 'delivered')
            ->where('created_at', '>=', now()->subDays(30))
            ->latest()
            ->get()
            ->map(fn ($order) => [
                'public_id' => $order->public_id,
                'order_number' => $order->order_number,
                'created_at' => $order->created_at->toDateTimeString(),
                'items' => $order->items->map(fn ($item) => [
                    'id' => $item->id,
                    'name' => $item->name,
                    'quantity' => $item->quantity,
                    'unit_price' => $item->unit_price,
                    'product_url' => $item->product ? '/urun/'.$item->product->public_id : null,
                ]),
            ]);

        return Inertia::render('Account/Returns', [
            'returns' => $returns,
            'eligibleOrders' => $eligibleOrders,
        ]);
    }

    public function storeReturn(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'order_public_id' => ['required', 'string', 'exists:orders,public_id'],
            'customer_note' => ['nullable', 'string', 'max:1000'],
            'items' => ['required', 'array', 'min:1'],
            'items.*.order_item_id' => ['required', 'integer', 'exists:order_items,id'],
            'items.*.quantity' => ['required', 'integer', 'min:1'],
            'items.*.reason' => ['required', 'string', 'max:255'],
        ]);

        $user = $this->currentUser();
        $order = Order::where('public_id', $validated['order_public_id'])
            ->where('user_id', $user->id)
            ->firstOrFail();

        $return = OrderReturn::create([
            'order_id' => $order->id,
            'user_id' => $user->id,
            'status' => 'pending',
            'customer_note' => $validated['customer_note'] ?? null,
        ]);

        foreach ($validated['items'] as $itemData) {
            $return->items()->create([
                'order_item_id' => $itemData['order_item_id'],
                'quantity' => $itemData['quantity'],
                'reason' => $itemData['reason'],
            ]);
        }

        return back()->with('success', 'İade talebiniz başarıyla oluşturuldu.');
    }

    private function currentUser(): User
    {
        $user = auth()->user();

        abort_unless($user instanceof User, 401);

        return $user;
    }
}
