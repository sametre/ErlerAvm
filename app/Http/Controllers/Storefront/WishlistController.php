<?php

namespace App\Http\Controllers\Storefront;

use App\Http\Controllers\Controller;
use App\Models\Product;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class WishlistController extends Controller
{
    public function toggle(Request $request, Product $product): RedirectResponse
    {
        $user = $request->user();

        $exists = $user->wishlistItems()->where('product_id', $product->id)->exists();

        if ($exists) {
            $user->wishlistItems()->where('product_id', $product->id)->delete();
            $message = 'Ürün favorilerden çıkarıldı.';
        } else {
            $user->wishlistItems()->create(['product_id' => $product->id]);
            $message = 'Ürün favorilere eklendi.';
        }

        return back()->with('toast', [
            'type' => 'success',
            'message' => $message,
        ]);
    }
}
