<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\InventoryMovement;
use App\Models\NewsletterSubscription;
use App\Models\Order;
use App\Models\PaymentAttempt;
use App\Models\Product;
use App\Models\StorefrontBanner;
use App\Models\StorefrontNavigationItem;
use App\Models\User;
use App\Models\VisitorSession;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;
use Inertia\Response;

class DashboardController extends Controller
{
    public function __invoke(): Response
    {
        $totalRevenue = Order::query()->where('payment_status', 'paid')->sum('grand_total');
        $paidOrders = Order::query()->where('payment_status', 'paid')->count();
        $todayRevenue = Order::query()
            ->where('payment_status', 'paid')
            ->whereDate('created_at', today())
            ->sum('grand_total');
        $monthRevenue = Order::query()
            ->where('payment_status', 'paid')
            ->whereBetween('created_at', [now()->startOfMonth(), now()->endOfMonth()])
            ->sum('grand_total');

        return Inertia::render('Admin/Dashboard', [
            'totalRevenue' => $totalRevenue,
            'revenueSummary' => [
                'grossRevenue' => (float) $totalRevenue,
                'todayRevenue' => (float) $todayRevenue,
                'monthRevenue' => (float) $monthRevenue,
                'paidOrders' => $paidOrders,
                'averageOrderValue' => $paidOrders > 0 ? round((float) $totalRevenue / $paidOrders, 2) : 0,
                'pendingPaymentRevenue' => (float) Order::query()->where('payment_status', 'pending')->sum('grand_total'),
                'last7Days' => $this->last7DaysRevenue(),
            ],
            'metrics' => [
                ['label' => 'Toplam ürün', 'value' => Product::query()->count(), 'tone' => 'default'],
                ['label' => 'Düşük stok', 'value' => Product::query()->where('stock_quantity', '<=', 5)->count(), 'tone' => 'warning'],
                ['label' => 'Sipariş', 'value' => Order::query()->count(), 'tone' => 'default'],
                ['label' => 'Ödenmiş ödeme', 'value' => PaymentAttempt::query()->where('status', 'paid')->count(), 'tone' => 'success'],
                ['label' => 'Bülten aboneleri', 'value' => NewsletterSubscription::query()->count(), 'tone' => 'default'],
                ['label' => 'Aktif ziyaretçi', 'value' => VisitorSession::query()->where('last_seen_at', '>=', now()->subMinutes(10))->count(), 'tone' => 'success'],
                ['label' => 'Admin kullanıcıları', 'value' => User::query()->where('is_admin', true)->count(), 'tone' => 'default'],
            ],
            'operations' => [
                'publishedProducts' => Product::query()->where('status', 'published')->count(),
                'publishedMenus' => StorefrontNavigationItem::query()->where('status', 'published')->count(),
                'publishedBanners' => StorefrontBanner::query()->where('status', 'published')->count(),
                'recentStockMovements' => InventoryMovement::query()->count(),
            ],
            'recentOrders' => Order::query()
                ->latest()
                ->limit(8)
                ->get(['public_id', 'order_number', 'email', 'status', 'payment_status', 'grand_total', 'currency', 'created_at']),
            'lowStockProducts' => Product::query()
                ->orderBy('stock_quantity')
                ->limit(8)
                ->get(['public_id', 'name', 'sku', 'stock_quantity', 'status']),
            'trafficMap' => $this->trafficMap(),
        ]);
    }

    /**
     * @return array<string, mixed>
     */
    private function trafficMap(): array
    {
        $recentOrders = Order::query()
            ->latest()
            ->limit(20)
            ->get(['order_number', 'shipping_address', 'grand_total', 'currency', 'created_at']);

        $unknownOrderLocations = 0;

        $orderPoints = $recentOrders
            ->map(function (Order $order) use (&$unknownOrderLocations): ?array {
                $city = (string) data_get($order->shipping_address, 'city', '');
                $district = (string) data_get($order->shipping_address, 'district', '');
                $position = $this->cityPosition($city);

                if (! $position) {
                    $unknownOrderLocations++;

                    return null;
                }

                return [
                    'type' => 'order',
                    'label' => trim($district.' / '.$city, ' /'),
                    'description' => $order->order_number.' · '.$order->grand_total.' '.$order->currency,
                    'lat' => $position[0],
                    'lon' => $position[1],
                    'created_at' => $order->created_at?->diffForHumans(),
                ];
            })
            ->filter()
            ->values();

        $activeVisitors = VisitorSession::query()
            ->where('last_seen_at', '>=', now()->subMinutes(10))
            ->latest('last_seen_at')
            ->limit(12)
            ->get(['last_path', 'last_seen_at']);

        return [
            'activeVisitors' => $activeVisitors->count(),
            'unknownOrderLocations' => $unknownOrderLocations,
            'points' => $orderPoints,
            'recentVisitorPaths' => $activeVisitors
                ->map(fn (VisitorSession $session) => [
                    'path' => $session->last_path ?: '/',
                    'last_seen_at' => $session->last_seen_at?->diffForHumans(),
                ])
                ->values(),
        ];
    }

    /**
     * @return array<int, array{date: string, label: string, revenue: float, orders: int}>
     */
    private function last7DaysRevenue(): array
    {
        $start = now()->subDays(6)->startOfDay();
        $rows = Order::query()
            ->selectRaw('CAST(created_at AS DATE) as revenue_date, SUM(grand_total) as revenue, COUNT(*) as orders_count')
            ->where('payment_status', 'paid')
            ->where('created_at', '>=', $start)
            ->groupBy(DB::raw('CAST(created_at AS DATE)'))
            ->pluck('revenue', 'revenue_date');

        $counts = Order::query()
            ->selectRaw('CAST(created_at AS DATE) as revenue_date, COUNT(*) as orders_count')
            ->where('payment_status', 'paid')
            ->where('created_at', '>=', $start)
            ->groupBy(DB::raw('CAST(created_at AS DATE)'))
            ->pluck('orders_count', 'revenue_date');

        return collect(range(0, 6))
            ->map(function (int $offset) use ($start, $rows, $counts): array {
                $date = Carbon::parse($start)->addDays($offset);
                $key = $date->toDateString();

                return [
                    'date' => $key,
                    'label' => $date->translatedFormat('d M'),
                    'revenue' => round((float) ($rows[$key] ?? 0), 2),
                    'orders' => (int) ($counts[$key] ?? 0),
                ];
            })
            ->all();
    }

    /**
     * @return array{0: float, 1: float}|null
     */
    private function cityPosition(string $city): ?array
    {
        return match (str($city)->lower()->ascii()->toString()) {
            'istanbul' => [41.0082, 28.9784],
            'ankara' => [39.9334, 32.8597],
            'izmir' => [38.4237, 27.1428],
            'bursa' => [40.1826, 29.0665],
            'balikesir' => [39.6484, 27.8826],
            'canakkale' => [40.1553, 26.4142],
            'eskisehir' => [39.7767, 30.5206],
            'kocaeli' => [40.8533, 29.8815],
            'sakarya' => [40.7569, 30.3781],
            'antalya' => [36.8969, 30.7133],
            'konya' => [37.8746, 32.4932],
            'adana' => [37.0, 35.3213],
            default => null,
        };
    }
}
