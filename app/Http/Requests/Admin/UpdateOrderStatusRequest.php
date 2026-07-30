<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateOrderStatusRequest extends FormRequest
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
            'status' => ['required', 'string', Rule::in([
                'payment_pending',
                'processing',
                'shipped',
                'completed',
                'cancelled',
            ])],
            'payment_status' => ['required', 'string', Rule::in([
                'pending',
                'paid',
                'failed',
                'refunded',
            ])],
            'admin_note' => ['nullable', 'string', 'max:1000'],
        ];
    }
}
