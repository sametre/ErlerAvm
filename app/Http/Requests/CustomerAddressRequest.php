<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class CustomerAddressRequest extends FormRequest
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
            'type' => ['required', Rule::in(['individual', 'corporate'])],
            'title' => ['required', 'string', 'max:120'],
            'full_name' => ['required', 'string', 'max:180'],
            'phone' => ['required', 'string', 'max:32', 'regex:/^[0-9+\s().-]{10,32}$/'],
            'city' => ['required', 'string', 'max:100'],
            'district' => ['required', 'string', 'max:100'],
            'postal_code' => ['nullable', 'string', 'max:20'],
            'address_line' => ['required', 'string', 'min:10', 'max:1000'],
            'is_default' => ['sometimes', 'boolean'],
        ];
    }

    /**
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'phone.regex' => 'Telefon numarası geçerli bir formatta olmalıdır.',
            'address_line.min' => 'Açık adres en az 10 karakter olmalıdır.',
        ];
    }
}
