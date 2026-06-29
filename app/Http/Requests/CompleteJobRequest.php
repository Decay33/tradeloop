<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class CompleteJobRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'completed_at' => ['nullable', 'date'],
            'schedule_followups' => ['nullable', 'boolean'],
            'followups' => ['nullable', 'array'],
            'followups.*.channel' => ['required_with:followups', Rule::in(['sms', 'email'])],
            'followups.*.purpose' => ['required_with:followups', 'string', 'max:80'],
            'followups.*.scheduled_at' => ['required_with:followups', 'date'],
            'followups.*.recipient' => ['nullable', 'string', 'max:255'],
            'followups.*.subject' => ['nullable', 'string', 'max:255'],
            'followups.*.body' => ['required_with:followups', 'string'],
            'followups.*.template_id' => ['nullable', 'integer'],
        ];
    }
}
