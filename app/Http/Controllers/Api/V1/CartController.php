<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Traits\ApiResponse;
use App\Models\Product;
use App\Services\Api\ApiCartService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class CartController extends Controller
{
    use ApiResponse;

    public function __construct(private readonly ApiCartService $cart) {}

    public function index(Request $request): JsonResponse
    {
        return $this->success($this->cart->summary($request->user()));
    }

    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'public_id' => ['required', 'string', 'exists:products,public_id'],
            'quantity' => ['required', 'integer', 'min:1'],
        ]);

        $product = Product::where('public_id', $validated['public_id'])->firstOrFail();

        if ($this->cart->add($request->user(), $product, $validated['quantity'])) {
            return $this->success($this->cart->summary($request->user()), 'Ürün sepete eklendi.');
        }

        return $this->error('Ürün sepete eklenemedi (stok yetersiz olabilir).');
    }

    public function update(Request $request, string $publicId): JsonResponse
    {
        $validated = $request->validate([
            'quantity' => ['required', 'integer', 'min:1'],
        ]);

        $product = Product::where('public_id', $publicId)->firstOrFail();

        if ($this->cart->update($request->user(), $product, $validated['quantity'])) {
            return $this->success($this->cart->summary($request->user()), 'Sepet güncellendi.');
        }

        return $this->error('Stok yetersiz.');
    }

    public function destroy(Request $request, string $publicId): JsonResponse
    {
        $product = Product::where('public_id', $publicId)->firstOrFail();

        $this->cart->remove($request->user(), $product);

        return $this->success($this->cart->summary($request->user()), 'Ürün sepetten çıkarıldı.');
    }
}
