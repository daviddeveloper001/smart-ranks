<?php

namespace App\Http\Requests\Api\Auth;

use Illuminate\Validation\Rule;
use Illuminate\Foundation\Http\FormRequest;

class RegisterRequest extends FormRequest
{

    public function rules(): array
    {
        $rules = [
            'name' => ['required', 'string', 'max:100'],
            'email' => ['required', 'email', 'unique:users,email'],
            'password' => ['required', 'string', 'min:6', 'confirmed'],
        ];

        if ($this->user() && $this->user()->hasRole('admin')) {
            $rules['role'] = ['sometimes', 'string', Rule::in(['admin', 'user'])];
        }

        return $rules;
    }

    protected function prepareForValidation()
    {
        if (! $this->user() && auth('api')->check()) {
            $this->setUserResolver(fn() => auth('api')->user());
        }
    }


    public function messages(): array
    {
        return [
            'role.in' => 'El rol debe ser "admin" o "user".',
            'password.confirmed' => 'La confirmación de contraseña no coincide.',
        ];
    }
}
