<?php

namespace App\Http\Requests\Storefront;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class CheckoutRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user() !== null;
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'shipping_full_name' => ['required', 'string', 'max:120'],
            'shipping_phone' => ['required', 'string', 'max:32'],
            'shipping_city' => ['required', 'string', 'max:80'],
            'shipping_district' => ['required', 'string', 'max:80'],
            'shipping_address_line' => ['required', 'string', 'max:1000'],
            'shipping_postal_code' => ['nullable', 'string', 'max:20'],

            'billing_type' => ['required', Rule::in(['individual', 'corporate'])],
            'billing_same_as_shipping' => ['boolean'],
            'billing_full_name' => ['required_if:billing_type,individual', 'nullable', 'string', 'max:120'],
            'billing_identity_number' => ['required_if:billing_type,individual', 'nullable', 'digits:11'],
            'billing_company_name' => ['required_if:billing_type,corporate', 'nullable', 'string', 'max:160'],
            'billing_tax_number' => ['required_if:billing_type,corporate', 'nullable', 'string', 'max:32'],
            'billing_tax_office' => ['required_if:billing_type,corporate', 'nullable', 'string', 'max:120'],
            'billing_city' => ['required_if:billing_same_as_shipping,false', 'nullable', 'string', 'max:80'],
            'billing_district' => ['required_if:billing_same_as_shipping,false', 'nullable', 'string', 'max:80'],
            'billing_address_line' => ['required_if:billing_same_as_shipping,false', 'nullable', 'string', 'max:1000'],
            'billing_postal_code' => ['nullable', 'string', 'max:20'],
            'notes' => ['nullable', 'string', 'max:1000'],
        ];
    }
}
