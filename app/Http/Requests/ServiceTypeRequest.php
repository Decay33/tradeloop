<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class ServiceTypeRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:255'],
            'category' => ['nullable', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
            'default_price' => ['nullable', 'numeric', 'min:0'],
            'default_repeat_months' => ['nullable', 'integer', 'min:1', 'max:120'],
            'is_active' => ['nullable', 'boolean'],
        ];
    }
}
