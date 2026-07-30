<?php

namespace App\Http\Requests\Admin;

use App\Models\User;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Validator;

class UpdateUserAccessRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        $user = $this->user();

        return $user instanceof User && $user->isAdmin();
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'roles' => ['sometimes', 'array'],
            'roles.*' => ['string', Rule::exists('roles', 'name')],
            'is_admin' => ['sometimes', 'boolean'],
            'admin_pin' => ['nullable', 'digits:6', 'confirmed'],
            'admin_pin_confirmation' => ['nullable', 'digits:6'],
        ];
    }

    /**
     * @return array<int, callable(Validator): void>
     */
    public function after(): array
    {
        return [
            function (Validator $validator): void {
                $target = $this->route('user');
                $actor = $this->user();

                if (! $target instanceof User || ! $actor instanceof User) {
                    return;
                }

                $isRemovingOwnAdminFlag = $target->is($actor)
                    && $this->has('is_admin')
                    && ! $this->boolean('is_admin');

                if ($isRemovingOwnAdminFlag) {
                    $validator->errors()->add('is_admin', 'Kendi admin erişiminizi bu ekrandan kaldıramazsınız.');
                }
            },
        ];
    }

    /**
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'admin_pin.digits' => 'Admin PIN tam 6 haneli olmalıdır.',
            'admin_pin.confirmed' => 'Admin PIN tekrarı eşleşmiyor.',
            'roles.*.exists' => 'Seçilen rol sistemde tanımlı değil.',
        ];
    }
}
