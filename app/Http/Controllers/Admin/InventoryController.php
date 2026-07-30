<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\AdjustProductStockRequest;
use App\Http\Requests\Admin\BulkProductActionRequest;
use App\Http\Requests\Admin\ProductAiRequest;
use App\Http\Requests\Admin\ProductRequest;
use App\Http\Requests\Admin\StoreBrandRequest;
use App\Models\Brand;
use App\Models\Category;
use App\Models\InventoryMovement;
use App\Models\OrderItem;
use App\Models\Product;
use App\Services\Ai\GeminiProductAssistant;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Inertia\Inertia;
use Inertia\Response;
use RuntimeException;

class InventoryController extends Controller
{
    public function index(Request $request): Response
    {
        $search = Str::of((string) $request->query('q', ''))->squish()->limit(80, '')->toString();
        $status = (string) $request->query('status', 'all');
        $stock = (string) $request->query('stock', 'all');
        $likeSearch = addcslashes($search, '\%_');

        return Inertia::render('Admin/Inventory', [
            'products' => Product::query()
                ->with(['brand:id,name', 'category:id,name'])
                ->withCount('orderItems')
                ->when($search !== '', fn ($query) => $query->where(function ($query) use ($likeSearch): void {
                    $query->where('name', 'like', "%{$likeSearch}%")
                        ->orWhere('sku', 'like', "%{$likeSearch}%");
                }))
                ->when(in_array($status, ['draft', 'published'], true), fn ($query) => $query->where('status', $status))
                ->when($stock === 'low', fn ($query) => $query->whereBetween('stock_quantity', [1, 5]))
                ->when($stock === 'out', fn ($query) => $query->where('stock_quantity', 0))
                ->orderBy('name')
                ->paginate(20, ['id', 'public_id', 'name', 'sku', 'price', 'compare_at_price', 'stock_quantity', 'status', 'brand_id', 'category_id', 'short_description', 'description', 'seo_title', 'seo_description', 'seo_keywords', 'images'])
                ->withQueryString()
                ->through(fn (Product $product) => [
                    'public_id' => $product->public_id,
                    'name' => $product->name,
                    'sku' => $product->sku,
                    'price' => $product->price,
                    'compare_at_price' => $product->compare_at_price,
                    'stock_quantity' => $product->stock_quantity,
                    'status' => $product->status,
                    'brand_id' => $product->brand_id,
                    'category_id' => $product->category_id,
                    'brand' => $product->brand?->name,
                    'category' => $product->category?->name,
                    'short_description' => $product->short_description,
                    'description' => $product->description,
                    'seo_title' => $product->seo_title,
                    'seo_description' => $product->seo_description,
                    'seo_keywords' => $product->seo_keywords,
                    'image_urls' => implode("\n", $product->images ?? []),
                    'order_items_count' => $product->order_items_count,
                ]),
            'recentMovements' => InventoryMovement::query()
                ->with(['product:id,name,public_id', 'user:id,name'])
                ->latest()
                ->limit(15)
                ->get()
                ->map(fn (InventoryMovement $movement) => [
                    'public_id' => $movement->public_id,
                    'product' => $movement->product?->name,
                    'user' => $movement->user?->name,
                    'quantity_delta' => $movement->quantity_delta,
                    'stock_before' => $movement->stock_before,
                    'stock_after' => $movement->stock_after,
                    'reason' => $movement->reason,
                    'note' => $movement->note,
                    'created_at' => $movement->created_at,
                ]),
            'categories' => Category::query()
                ->orderBy('name')
                ->get(['id', 'name']),
            'brands' => Brand::query()
                ->orderBy('name')
                ->get(['id', 'name']),
            'filters' => [
                'q' => $search,
                'status' => in_array($status, ['all', 'draft', 'published'], true) ? $status : 'all',
                'stock' => in_array($stock, ['all', 'low', 'out'], true) ? $stock : 'all',
            ],
        ]);
    }

    public function createProduct(): Response
    {
        return Inertia::render('Admin/ProductForm', [
            'product' => null,
            'categories' => Category::query()->orderBy('name')->get(['id', 'name']),
            'brands' => Brand::query()->orderBy('name')->get(['id', 'name']),
        ]);
    }

    public function editProduct(Product $product): Response
    {
        return Inertia::render('Admin/ProductForm', [
            'product' => [
                'id' => $product->id,
                'public_id' => $product->public_id,
                'name' => $product->name,
                'sku' => $product->sku,
                'price' => $product->price,
                'compare_at_price' => $product->compare_at_price,
                'stock_quantity' => $product->stock_quantity,
                'status' => $product->status,
                'brand_id' => $product->brand_id,
                'category_id' => $product->category_id,
                'short_description' => $product->short_description,
                'description' => $product->description,
                'features' => $product->features,
                'seo_title' => $product->seo_title,
                'seo_description' => $product->seo_description,
                'seo_keywords' => $product->seo_keywords,
                'image_urls' => implode("\n", $product->images ?? []),
                'variants' => $product->variants->map(fn ($v) => [
                    'sku' => $v->sku,
                    'price' => $v->price,
                    'compare_at_price' => $v->compare_at_price,
                    'stock_quantity' => $v->stock_quantity,
                    'options' => $v->options,
                ])->toArray(),
            ],
            'categories' => Category::query()->orderBy('name')->get(['id', 'name']),
            'brands' => Brand::query()->orderBy('name')->get(['id', 'name']),
        ]);
    }

    public function storeProduct(ProductRequest $request): RedirectResponse
    {
        $data = $this->validatedProductData($request);

        DB::transaction(function () use ($data, $request): void {
            $variants = $data['variants'] ?? [];
            unset($data['variants']);

            if (count($variants) > 0) {
                $data['stock_quantity'] = collect($variants)->sum('stock_quantity');
            }

            $product = Product::query()->create($data);

            if (count($variants) > 0) {
                foreach ($variants as $variant) {
                    $product->variants()->create([
                        'sku' => $variant['sku'] ?? null,
                        'price' => $variant['price'] ?? null,
                        'compare_at_price' => $variant['compare_at_price'] ?? null,
                        'stock_quantity' => $variant['stock_quantity'],
                        'options' => $variant['options'],
                    ]);
                }
            }

            if ($product->stock_quantity > 0) {
                InventoryMovement::create([
                    'product_id' => $product->id,
                    'user_id' => $request->user()?->id,
                    'quantity_delta' => $product->stock_quantity,
                    'stock_before' => 0,
                    'stock_after' => $product->stock_quantity,
                    'reason' => 'product_create',
                    'note' => 'Ürün ekleme ekranından başlangıç stoğu.',
                ]);
            }

            logger()->info('Admin product created.', [
                'product_id' => $product->id,
                'admin_id' => $request->user()?->id,
            ]);
        });

        return redirect()->route('admin.inventory.index')->with('toast', [
            'type' => 'success',
            'message' => 'Ürün kaydedildi.',
        ]);
    }

    public function updateProduct(ProductRequest $request, Product $product): RedirectResponse
    {
        $data = $this->validatedProductData($request);

        DB::transaction(function () use ($data, $product, $request): void {
            $lockedProduct = Product::query()
                ->whereKey($product->id)
                ->lockForUpdate()
                ->firstOrFail();

            $variants = $data['variants'] ?? [];
            unset($data['variants']);

            if (count($variants) > 0) {
                $data['stock_quantity'] = collect($variants)->sum('stock_quantity');
            }

            $stockBefore = $lockedProduct->stock_quantity;
            $stockAfter = (int) $data['stock_quantity'];

            $lockedProduct->update($data);

            if (count($variants) > 0) {
                $lockedProduct->variants()->delete();
                foreach ($variants as $variant) {
                    $lockedProduct->variants()->create([
                        'sku' => $variant['sku'] ?? null,
                        'price' => $variant['price'] ?? null,
                        'compare_at_price' => $variant['compare_at_price'] ?? null,
                        'stock_quantity' => $variant['stock_quantity'],
                        'options' => $variant['options'],
                    ]);
                }
            }

            if ($stockBefore !== $stockAfter) {
                InventoryMovement::create([
                    'product_id' => $lockedProduct->id,
                    'user_id' => $request->user()?->id,
                    'quantity_delta' => $stockAfter - $stockBefore,
                    'stock_before' => $stockBefore,
                    'stock_after' => $stockAfter,
                    'reason' => 'manual_correction',
                    'note' => 'Ürün düzenleme ekranından stok güncellendi.',
                ]);
            }

            logger()->info('Admin product updated.', [
                'product_id' => $lockedProduct->id,
                'admin_id' => $request->user()?->id,
            ]);
        });

        return redirect()->route('admin.inventory.index')->with('toast', [
            'type' => 'success',
            'message' => 'Ürün güncellendi.',
        ]);
    }

    public function destroyProduct(Request $request, Product $product): RedirectResponse
    {
        if (OrderItem::query()->where('product_id', $product->id)->exists()) {
            $product->update([
                'status' => 'draft',
                'published_at' => null,
            ]);

            logger()->warning('Admin product delete converted to unpublish because order history exists.', [
                'product_id' => $product->id,
                'admin_id' => $request->user()?->id,
            ]);

            return back()->with('toast', [
                'type' => 'warning',
                'message' => 'Ürün sipariş geçmişinde olduğu için silinmedi, yayından kaldırıldı.',
            ]);
        }

        $product->delete();

        logger()->info('Admin product deleted.', [
            'product_id' => $product->id,
            'admin_id' => $request->user()?->id,
        ]);

        return back()->with('toast', [
            'type' => 'success',
            'message' => 'Ürün silindi.',
        ]);
    }

    public function adjust(AdjustProductStockRequest $request, Product $product): RedirectResponse
    {
        $data = $request->validated();

        DB::transaction(function () use ($data, $product, $request): void {
            $lockedProduct = Product::query()
                ->whereKey($product->id)
                ->lockForUpdate()
                ->firstOrFail();

            $stockBefore = $lockedProduct->stock_quantity;
            $stockAfter = $stockBefore + $data['quantity_delta'];

            if ($stockAfter < 0) {
                abort(422, 'Stok adedi sıfırın altına düşemez.');
            }

            $lockedProduct->update(['stock_quantity' => $stockAfter]);

            InventoryMovement::create([
                'product_id' => $lockedProduct->id,
                'user_id' => $request->user()?->id,
                'quantity_delta' => $data['quantity_delta'],
                'stock_before' => $stockBefore,
                'stock_after' => $stockAfter,
                'reason' => $data['reason'],
                'note' => $data['note'] ?? null,
            ]);
        });

        return back()->with('toast', [
            'type' => 'success',
            'message' => 'Stok hareketi kaydedildi.',
        ]);
    }

    public function bulkAction(BulkProductActionRequest $request): RedirectResponse
    {
        $data = $request->validated();

        DB::transaction(function () use ($data, $request): void {
            $products = Product::query()
                ->whereIn('public_id', $data['product_ids'])
                ->lockForUpdate()
                ->get();

            if (in_array($data['action'], ['publish', 'draft'], true)) {
                $products->each(function (Product $product) use ($data): void {
                    $product->update([
                        'status' => $data['action'] === 'publish' ? 'published' : 'draft',
                        'published_at' => $data['action'] === 'publish' ? now() : null,
                    ]);
                });

                return;
            }

            $delta = (int) $data['quantity_delta'];
            $delta = $data['action'] === 'stock_decrease' ? -$delta : $delta;

            foreach ($products as $product) {
                $stockBefore = $product->stock_quantity;
                $stockAfter = $stockBefore + $delta;

                if ($stockAfter < 0) {
                    abort(422, "{$product->name} için stok sıfırın altına düşemez.");
                }

                $product->update(['stock_quantity' => $stockAfter]);

                InventoryMovement::create([
                    'product_id' => $product->id,
                    'user_id' => $request->user()?->id,
                    'quantity_delta' => $delta,
                    'stock_before' => $stockBefore,
                    'stock_after' => $stockAfter,
                    'reason' => 'bulk_admin_action',
                    'note' => $data['note'] ?? 'Toplu stok işlemi.',
                ]);
            }
        });

        logger()->info('Admin bulk product action completed.', [
            'admin_id' => $request->user()?->id,
            'action' => $data['action'],
            'count' => count($data['product_ids']),
        ]);

        return back()->with('toast', [
            'type' => 'success',
            'message' => 'Toplu işlem tamamlandı.',
        ]);
    }

    public function storeBrand(StoreBrandRequest $request): RedirectResponse
    {
        $brand = Brand::query()->create($request->validated());

        logger()->info('Admin brand created from inventory drawer.', [
            'brand_id' => $brand->id,
            'admin_id' => $request->user()?->id,
        ]);

        return back()->with('toast', [
            'type' => 'success',
            'message' => 'Marka oluşturuldu. Ürün formundaki marka listesinden seçebilirsiniz.',
        ]);
    }

    public function generateProductCopy(ProductAiRequest $request, GeminiProductAssistant $assistant): JsonResponse
    {
        try {
            $result = $assistant->generateProductCopy($request->validated());
        } catch (RuntimeException $exception) {
            return response()->json([
                'message' => $exception->getMessage(),
            ], 422);
        }

        logger()->info('Admin generated product copy with Gemini.', [
            'admin_id' => $request->user()?->id,
            'product_name' => $request->input('name'),
        ]);

        return response()->json($result);
    }

    /**
     * @return array<string, mixed>
     */
    private function validatedProductData(ProductRequest $request): array
    {
        $validated = $request->validated();
        $status = $validated['status'];

        $images = Str::of((string) ($validated['image_urls'] ?? ''))
            ->replace(["\r\n", "\r", "\n"], ',')
            ->explode(',')
            ->map(fn (string $value) => trim($value))
            ->filter(fn (string $value) => $value !== '' && preg_match('/^(\/|https?:\/\/)/', $value) === 1)
            ->values()
            ->all();

        unset($validated['image_urls']);

        $uploadedImages = [];
        if ($request->hasFile('images')) {
            foreach ($request->file('images') as $file) {
                if ($file->isValid()) {
                    $path = $file->store('products', 'public');
                    $uploadedImages[] = Storage::url($path);
                }
            }
        }

        $images = array_merge($images, $uploadedImages);
        unset($validated['images']);

        $seoTitle = Str::of((string) ($validated['seo_title'] ?? ''))
            ->squish()
            ->whenEmpty(fn () => Str::of((string) $validated['name'])->squish())
            ->limit(70, '')
            ->toString();

        $seoDescription = Str::of((string) ($validated['seo_description'] ?? ''))
            ->stripTags()
            ->squish()
            ->whenEmpty(fn () => Str::of((string) ($validated['short_description'] ?? $validated['description'] ?? $validated['name']))
                ->stripTags()
                ->squish())
            ->limit(160, '')
            ->toString();

        $seoKeywords = Str::of((string) ($validated['seo_keywords'] ?? ''))
            ->squish()
            ->whenEmpty(fn () => Str::of($validated['name'].', ErlerAVM, Karacabey alışveriş')->squish())
            ->limit(240, '')
            ->toString();

        return [
            ...$validated,
            'category_id' => $validated['category_id'] ?: null,
            'brand_id' => $validated['brand_id'] ?: null,
            'compare_at_price' => $validated['compare_at_price'] ?: null,
            'seo_title' => $seoTitle,
            'seo_description' => $seoDescription,
            'seo_keywords' => $seoKeywords,
            'images' => $images,
            'published_at' => $status === 'published' ? now() : null,
            'variants' => $validated['variants'] ?? [],
        ];
    }
}
