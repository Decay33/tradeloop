<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class FollowupTemplateRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:255'],
            'channel' => ['required', Rule::in(['sms', 'email'])],
            'purpose' => ['required', Rule::in(['thank_you', 'review_request', 'repeat_service', 'warranty_check', 'seasonal_reminder', 'custom'])],
            'subject' => ['nullable', 'string', 'max:255'],
            'body' => ['required', 'string'],
            'is_default' => ['nullable', 'boolean'],
        ];
    }
}
