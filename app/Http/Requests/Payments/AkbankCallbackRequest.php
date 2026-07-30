<?php

namespace App\Http\Requests\Payments;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class AkbankCallbackRequest extends FormRequest
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
            'oid' => ['required_without:merchant_oid', 'nullable', 'string', 'max:64'],
            'merchant_oid' => ['required_without:oid', 'nullable', 'string', 'max:64'],
            'Response' => ['nullable', 'string', 'max:64'],
            'ProcReturnCode' => ['nullable', 'string', 'max:32'],
            'ErrMsg' => ['nullable', 'string', 'max:1000'],
            'mdStatus' => ['nullable', 'string', 'max:16'],
            'currency' => ['nullable', 'string', 'max:8'],
            'amount' => ['nullable', 'string', 'max:32'],
            'Amount' => ['nullable', 'string', 'max:32'],
            'total_amount' => ['nullable', 'string', 'max:32'],
            'TransId' => ['nullable', 'string', 'max:128'],
            'AuthCode' => ['nullable', 'string', 'max:64'],
            'HostRefNum' => ['nullable', 'string', 'max:128'],
            'MaskedPan' => ['nullable', 'string', 'max:32'],
            'maskedPan' => ['nullable', 'string', 'max:32'],
            'cardType' => ['nullable', 'string', 'max:32'],
            'CARDTYPE' => ['nullable', 'string', 'max:32'],
            'taksit' => ['nullable', 'integer', 'min:0', 'max:12'],
            'installment' => ['nullable', 'integer', 'min:0', 'max:12'],
            'HASHPARAMS' => ['required', 'string'],
            'HASHPARAMSVAL' => ['nullable', 'string'],
            'HASH' => ['required_without:hash', 'nullable', 'string'],
            'hash' => ['required_without:HASH', 'nullable', 'string'],
        ];
    }

    public function result(): string
    {
        return (string) $this->route('result');
    }

    public function validateResolved(): void
    {
        parent::validateResolved();

        validator(
            ['result' => $this->result()],
            ['result' => ['required', Rule::in(['success', 'fail'])]],
        )->validate();
    }

    /**
     * @return array<string, mixed>
     */
    public function paymentPayload(): array
    {
        return $this->validated();
    }
}
