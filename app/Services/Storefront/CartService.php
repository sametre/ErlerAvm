<?php

namespace App\Services\Storefront;

use App\Models\Product;
use Illuminate\Support\Collection;

class CartService
{
    private const SESSION_KEY = 'storefront_cart';

    /**
     * @return array{items: array<int, array<string, mixed>>, subtotal: string, count: int}
     */
    public function summary(): array
    {
        $quantities = $this->quantities();
        $products = $this->products($quantities);

        $items = $products
            ->map(function (Product $product) use ($quantities): array {
                $quantity = $quantities[$product->id] ?? 0;
                $lineTotal = (float) $product->price * $quantity;

                return [
                    'public_id' => $product->public_id,
                    'name' => $product->name,
                    'price' => $product->price,
                    'quantity' => $quantity,
                    'line_total' => number_format($lineTotal, 2, '.', ''),
                    'stock_quantity' => $product->stock_quantity,
                    'image' => $product->images[0] ?? null,
                    'url' => $product->url,
                ];
            })
            ->values()
            ->all();

        $subtotal = collect($items)->sum(fn (array $item) => (float) $item['line_total']);

        return [
            'items' => $items,
            'subtotal' => number_format($subtotal, 2, '.', ''),
            'count' => array_sum($quantities),
        ];
    }

    public function add(Product $product, int $quantity = 1): bool
    {
        if ($product->status !== 'published' || $product->published_at === null || $product->stock_quantity <= 0) {
            return false;
        }

        $quantities = $this->quantities();
        $current = $quantities[$product->id] ?? 0;
        $next = min($product->stock_quantity, $current + max(1, $quantity));

        if ($next <= $current) {
            return false;
        }

        $quantities[$product->id] = $next;
        $this->store($quantities);

        return true;
    }

    public function update(Product $product, int $quantity): bool
    {
        $quantities = $this->quantities();

        if ($quantity <= 0) {
            unset($quantities[$product->id]);
            $this->store($quantities);

            return true;
        }

        if ($product->stock_quantity <= 0) {
            return false;
        }

        $quantities[$product->id] = min($product->stock_quantity, $quantity);
        $this->store($quantities);

        return true;
    }

    public function remove(Product $product): void
    {
        $quantities = $this->quantities();
        unset($quantities[$product->id]);
        $this->store($quantities);
    }

    public function clear(): void
    {
        session()->forget(self::SESSION_KEY);
    }

    /**
     * @return array<int, int>
     */
    public function rawQuantities(): array
    {
        return $this->quantities();
    }

    /**
     * @return array<int, int>
     */
    private function quantities(): array
    {
        $cart = session(self::SESSION_KEY, []);

        if (! is_array($cart)) {
            return [];
        }

        return collect($cart)
            ->mapWithKeys(fn ($quantity, $id) => [(int) $id => max(0, (int) $quantity)])
            ->filter(fn (int $quantity) => $quantity > 0)
            ->all();
    }

    /**
     * @param  array<int, int>  $quantities
     * @return Collection<int, Product>
     */
    private function products(array $quantities): Collection
    {
        if ($quantities === []) {
            return collect();
        }

        return Product::query()
            ->whereIn('id', array_keys($quantities))
            ->where('status', 'published')
            ->whereNotNull('published_at')
            ->get(['id', 'public_id', 'name', 'slug', 'price', 'stock_quantity', 'images', 'status', 'published_at']);
    }

    /**
     * @param  array<int, int>  $quantities
     */
    private function store(array $quantities): void
    {
        session()->put(self::SESSION_KEY, $quantities);
    }
}
