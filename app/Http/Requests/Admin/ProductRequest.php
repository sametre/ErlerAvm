<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class ProductRequest extends FormRequest
{
    protected function prepareForValidation(): void
    {
        $this->merge([
            'sku' => $this->filled('sku') ? $this->input('sku') : null,
            'category_id' => $this->filled('category_id') ? $this->input('category_id') : null,
            'brand_id' => $this->filled('brand_id') ? $this->input('brand_id') : null,
            'compare_at_price' => $this->filled('compare_at_price') ? $this->input('compare_at_price') : null,
        ]);
    }

    public function authorize(): bool
    {
        return $this->user()?->is_admin === true;
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        $productId = $this->route('product')?->id;

        return [
            'name' => ['required', 'string', 'max:180'],
            'sku' => [
                'nullable',
                'string',
                'max:80',
                Rule::unique('products', 'sku')->ignore($productId),
            ],
            'category_id' => ['nullable', 'integer', Rule::exists('categories', 'id')],
            'brand_id' => ['nullable', 'integer', Rule::exists('brands', 'id')],
            'price' => ['required', 'numeric', 'min:0', 'max:999999999.99'],
            'compare_at_price' => ['nullable', 'numeric', 'min:0', 'max:999999999.99'],
            'stock_quantity' => ['required', 'integer', 'min:0', 'max:1000000'],
            'status' => ['required', 'string', Rule::in(['draft', 'published'])],
            'short_description' => ['nullable', 'string', 'max:500'],
            'description' => ['nullable', 'string', 'max:5000'],
            'seo_title' => ['nullable', 'string', 'max:180'],
            'seo_description' => ['nullable', 'string', 'max:320'],
            'seo_keywords' => ['nullable', 'string', 'max:500'],
            'image_urls' => ['nullable', 'string', 'max:10000'],
            'images' => ['nullable', 'array', 'max:20'],
            'images.*' => ['file', 'mimes:jpeg,png,jpg,gif,svg,webp,avif', 'max:6144'],
            'features' => ['nullable', 'string', 'max:50000'],
            'variants' => ['nullable', 'array'],
            'variants.*.sku' => ['nullable', 'string', 'max:80'],
            'variants.*.price' => ['nullable', 'numeric', 'min:0', 'max:999999999.99'],
            'variants.*.compare_at_price' => ['nullable', 'numeric', 'min:0', 'max:999999999.99'],
            'variants.*.stock_quantity' => ['required', 'integer', 'min:0', 'max:1000000'],
            'variants.*.options' => ['required', 'array'],
        ];
    }
}
