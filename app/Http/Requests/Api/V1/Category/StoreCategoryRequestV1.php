<?php

namespace App\Http\Requests\Api\V1\Category;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Auth\Access\AuthorizationException;

class StoreCategoryRequestV1 extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->hasRole('admin');
    }

    protected function failedAuthorization()
    {
        throw new AuthorizationException('Only administrators can create categories.');
    }

    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:100', 'unique:categories,name'],
            'description' => ['nullable', 'string', 'max:1000'],
        ];
    }

    public function messages()
    {
        return [
            'name.required' => 'The name field is required.',
            'name.string' => 'The name field must be a string.',
            'name.max' => 'The name field must not be greater than 100 characters.',
            'name.unique' => 'The name field must be unique.',
            'description.string' => 'The description field must be a string.',
            'description.max' => 'The description field must not be greater than 1000 characters.',
        ];
    }
}
