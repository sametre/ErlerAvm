<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class BulkProductActionRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->is_admin === true;
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'product_ids' => ['required', 'array', 'min:1', 'max:100'],
            'product_ids.*' => ['required', 'string', Rule::exists('products', 'public_id')],
            'action' => ['required', 'string', Rule::in(['publish', 'draft', 'stock_increase', 'stock_decrease'])],
            'quantity_delta' => ['nullable', 'integer', 'min:1', 'max:1000000', 'required_if:action,stock_increase,stock_decrease'],
            'note' => ['nullable', 'string', 'max:500'],
        ];
    }
}
