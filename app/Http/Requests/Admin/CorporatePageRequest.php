<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class CorporatePageRequest extends FormRequest
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
            'title' => ['required', 'string', 'max:255'],
            'content' => ['nullable', 'string', 'max:50000'],
            'status' => ['required', 'string', Rule::in(['draft', 'published'])],
            'is_active' => ['required', 'boolean'],
            'seo_meta' => ['nullable', 'array'],
            'seo_meta.description' => ['nullable', 'string', 'max:320'],
        ];
    }
}
