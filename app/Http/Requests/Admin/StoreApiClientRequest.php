<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreApiClientRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->can('api-clients.manage') === true
            || $this->user()?->hasRole('Super Admin') === true;
    }

    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:120'],
            'abilities' => ['required', 'array', 'min:1'],
            'abilities.*' => ['string', 'distinct', Rule::in(array_keys(config('api.abilities')))],
            'allowed_ips' => ['nullable', 'array', 'max:20'],
            'allowed_ips.*' => ['ip'],
            'expires_at' => ['nullable', 'date', 'after:now'],
        ];
    }
}
