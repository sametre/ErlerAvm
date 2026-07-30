<?php

namespace App\Http\Requests\Api\V1\Admin;

use App\Models\User;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Validator;

class UpdateUserAccessRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->can('users.manage') === true || $this->user()?->hasRole('Super Admin') === true;
    }

    public function rules(): array
    {
        return [
            'roles' => ['present', 'array'],
            'roles.*' => ['string', 'distinct', Rule::exists('roles', 'name')],
            'permissions' => ['present', 'array'],
            'permissions.*' => ['string', 'distinct', Rule::exists('permissions', 'name')],
            'is_admin' => ['sometimes', 'boolean'],
        ];
    }

    public function after(): array
    {
        return [
            function (Validator $validator): void {
                $target = $this->route('user');

                if ($target instanceof User && $target->is($this->user()) && $this->has('is_admin') && $this->boolean('is_admin') === false) {
                    $validator->errors()->add('is_admin', 'Kendi yönetici erişiminizi kaldıramazsınız.');
                }

                if (! $this->user()?->hasRole('Super Admin') && in_array('Super Admin', $this->input('roles', []), true)) {
                    $validator->errors()->add('roles', 'Super Admin rolünü yalnızca bir Super Admin atayabilir.');
                }

                if ($target instanceof User && $target->hasRole('Super Admin') && ! $this->user()?->hasRole('Super Admin')) {
                    $validator->errors()->add('roles', 'Super Admin yetkilerini değiştiremezsiniz.');
                }
            },
        ];
    }
}
