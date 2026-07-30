<?php

namespace App\Services\Api;

use App\Models\Cart;
use App\Models\Product;
use App\Models\User;

class ApiCartService
{
    /**
     * @return array{items: array<int, array<string, mixed>>, subtotal: string, count: int}
     */
    public function summary(User $user): array
    {
        $cartItems = Cart::with('product:id,public_id,name,slug,price,stock_quantity,images,status,published_at')
            ->where('user_id', $user->id)
            ->get();

        // Filter out unpublished/deleted products and calculate total
        $validItems = collect();
        $subtotal = 0.0;
        $count = 0;

        foreach ($cartItems as $item) {
            $product = $item->product;

            if (! $product || $product->status !== 'published' || $product->published_at === null) {
                // Optionally remove invalid items from cart
                // $item->delete();
                continue;
            }

            $lineTotal = (float) $product->price * $item->quantity;
            $subtotal += $lineTotal;
            $count += $item->quantity;

            $validItems->push([
                'public_id' => $product->public_id,
                'name' => $product->name,
                'price' => $product->price,
                'quantity' => $item->quantity,
                'line_total' => number_format($lineTotal, 2, '.', ''),
                'stock_quantity' => $product->stock_quantity,
                'image' => $product->images[0] ?? null,
                'url' => $product->url,
            ]);
        }

        return [
            'items' => $validItems->all(),
            'subtotal' => number_format($subtotal, 2, '.', ''),
            'count' => $count,
        ];
    }

    public function add(User $user, Product $product, int $quantity = 1): bool
    {
        if ($product->status !== 'published' || $product->published_at === null || $product->stock_quantity <= 0) {
            return false;
        }

        $cartItem = Cart::firstOrNew([
            'user_id' => $user->id,
            'product_id' => $product->id,
        ]);

        $current = $cartItem->exists ? $cartItem->quantity : 0;
        $next = min($product->stock_quantity, $current + max(1, $quantity));

        if ($next <= $current) {
            return false;
        }

        $cartItem->quantity = $next;
        $cartItem->save();

        return true;
    }

    public function update(User $user, Product $product, int $quantity): bool
    {
        if ($quantity <= 0) {
            $this->remove($user, $product);

            return true;
        }

        if ($product->stock_quantity <= 0) {
            return false;
        }

        Cart::updateOrCreate(
            ['user_id' => $user->id, 'product_id' => $product->id],
            ['quantity' => min($product->stock_quantity, $quantity)]
        );

        return true;
    }

    public function remove(User $user, Product $product): void
    {
        Cart::where('user_id', $user->id)
            ->where('product_id', $product->id)
            ->delete();
    }

    public function clear(User $user): void
    {
        Cart::where('user_id', $user->id)->delete();
    }

    /**
     * @return array<int, int> [product_id => quantity]
     */
    public function rawQuantities(User $user): array
    {
        return Cart::where('user_id', $user->id)
            ->pluck('quantity', 'product_id')
            ->all();
    }
}
