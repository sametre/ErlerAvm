<?php

namespace App\Http\Controllers\Storefront;

use App\Http\Controllers\Controller;
use App\Http\Requests\Storefront\ProductReviewRequest;
use App\Models\Product;
use Illuminate\Http\RedirectResponse;

class ProductReviewController extends Controller
{
    public function store(ProductReviewRequest $request, Product $product): RedirectResponse
    {
        abort_unless($product->status === 'published' && $product->published_at !== null, 404);

        $validated = $request->validated();

        $product->reviews()->updateOrCreate(
            ['user_id' => $request->user()->id],
            [
                'rating' => (int) $request->input('rating', $request->rating ?? 5),
                'comment' => $request->input('comment'),
                'status' => 'pending',
            ]
        );

        logger()->info('Product review submitted for moderation.', [
            'product_id' => $product->id,
            'user_id' => $request->user()->id,
        ]);

        return back()->with('toast', [
            'type' => 'success',
            'message' => 'Yorumunuz başarıyla gönderildi ve onaylandıktan sonra yayınlanacaktır.',
        ]);
    }
}
