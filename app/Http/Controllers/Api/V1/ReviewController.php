<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Traits\ApiResponse;
use App\Models\Product;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ReviewController extends Controller
{
    use ApiResponse;

    public function store(Request $request, string $productPublicId): JsonResponse
    {
        $validated = $request->validate([
            'rating' => ['required', 'integer', 'min:1', 'max:5'],
            'comment' => ['required', 'string', 'max:1000'],
        ]);

        $product = Product::where('public_id', $productPublicId)
            ->where('status', 'published')
            ->firstOrFail();

        // Check if user has already reviewed this product
        if ($product->reviews()->where('user_id', $request->user()->id)->exists()) {
            return $this->error('Bu ürün için zaten bir yorum yaptınız.', 400);
        }

        // Check if user has purchased the product
        $hasPurchased = $request->user()->orders()
            ->whereIn('status', ['completed', 'shipped'])
            ->whereHas('items', function ($query) use ($product) {
                $query->where('product_id', $product->id);
            })->exists();

        $product->reviews()->create([
            'user_id' => $request->user()->id,
            'rating' => $validated['rating'],
            'comment' => $validated['comment'],
            'status' => 'pending', // Requires admin approval
            'is_verified_buyer' => $hasPurchased,
        ]);

        return $this->success(null, 'Yorumunuz değerlendirilmek üzere gönderildi.', 201);
    }
}
