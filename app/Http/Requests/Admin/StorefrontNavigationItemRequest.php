<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StorefrontNavigationItemRequest extends FormRequest
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
            'location' => ['required', 'string', Rule::in(['top', 'mega', 'footer'])],
            'title' => ['required', 'string', 'max:120'],
            'url' => ['required', 'string', 'max:500', 'regex:/^(\/|https?:\/\/)/'],
            'status' => ['required', 'string', Rule::in(['draft', 'published'])],
            'sort_order' => ['required', 'integer', 'min:0', 'max:100000'],
        ];
    }
}
