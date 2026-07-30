<?php

namespace App\Http\Requests\Payments;

use Illuminate\Foundation\Http\FormRequest;

class PaytrCallbackRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'merchant_oid' => ['required', 'string', 'max:64'],
            'status' => ['required', 'string', 'in:success,failed'],
            'total_amount' => ['required', 'integer', 'min:0'],
            'hash' => ['required', 'string'],
            'failed_reason_code' => ['nullable', 'string', 'max:32'],
            'failed_reason_msg' => ['nullable', 'string', 'max:1000'],
            'test_mode' => ['nullable'],
            'payment_type' => ['nullable', 'string', 'max:32'],
            'currency' => ['nullable', 'string', 'max:8'],
            'payment_amount' => ['nullable', 'integer', 'min:0'],
            'installment_count' => ['nullable', 'integer', 'min:0', 'max:12'],
        ];
    }
}
