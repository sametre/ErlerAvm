<?php

namespace App\Http\Controllers\Storefront;

use App\Http\Controllers\Controller;
use App\Models\Product;
use App\Services\Storefront\CartService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class CartController extends Controller
{
    public function show(CartService $cart): Response
    {
        return Inertia::render('Storefront/Cart', [
            'cart' => $cart->summary(),
        ]);
    }

    public function add(Request $request, Product $product, CartService $cart): RedirectResponse
    {
        $data = $request->validate([
            'quantity' => ['nullable', 'integer', 'min:1', 'max:99'],
        ]);

        if (! $cart->add($product, (int) ($data['quantity'] ?? 1))) {
            return back()->with('toast', [
                'type' => 'error',
                'message' => 'Ürün sepete eklenemedi. Stok durumunu kontrol edin.',
            ]);
        }

        return back()->with('toast', [
            'type' => 'success',
            'message' => 'Ürün sepete eklendi.',
        ]);
    }

    public function update(Request $request, Product $product, CartService $cart): RedirectResponse
    {
        $data = $request->validate([
            'quantity' => ['required', 'integer', 'min:0', 'max:99'],
        ]);

        if (! $cart->update($product, (int) $data['quantity'])) {
            return back()->with('toast', [
                'type' => 'error',
                'message' => 'Sepet güncellenemedi. Ürün stoğu yeterli değil.',
            ]);
        }

        return back()->with('toast', [
            'type' => 'success',
            'message' => 'Sepet güncellendi.',
        ]);
    }

    public function remove(Product $product, CartService $cart): RedirectResponse
    {
        $cart->remove($product);

        return back()->with('toast', [
            'type' => 'success',
            'message' => 'Ürün sepetten kaldırıldı.',
        ]);
    }
}
