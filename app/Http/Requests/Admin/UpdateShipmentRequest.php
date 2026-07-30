<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateShipmentRequest extends FormRequest
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
            'shipment_provider_id' => ['required', 'integer', Rule::exists('shipment_providers', 'id')->where('status', 'active')],
            'tracking_number' => ['required', 'string', 'max:120'],
            'status' => ['required', 'string', Rule::in(['preparing', 'shipped', 'delivered', 'cancelled', 'returned'])],
            'admin_note' => ['nullable', 'string', 'max:1000'],
            'notify_customer' => ['sometimes', 'boolean'],
        ];
    }
}
