<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class AdjustProductStockRequest extends FormRequest
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
            'quantity_delta' => ['required', 'integer', 'between:-100000,100000', 'not_in:0'],
            'reason' => [
                'required',
                'string',
                Rule::in(['manual_correction', 'purchase_entry', 'damage', 'return_entry', 'reservation_release']),
            ],
            'note' => ['nullable', 'string', 'max:1000'],
        ];
    }
}
