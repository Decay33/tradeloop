<?php

namespace App\Http\Requests;

use App\Services\CurrentBusinessResolver;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class JobRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $businessId = app(CurrentBusinessResolver::class)->id();

        return [
            'customer_id' => ['required', Rule::exists('customers', 'id')->where('business_id', $businessId)],
            'service_type_id' => ['required', Rule::exists('service_types', 'id')->where('business_id', $businessId)],
            'assigned_user_id' => ['nullable', Rule::exists('business_user', 'user_id')->where('business_id', $businessId)->where('is_active', true)],
            'title' => ['required', 'string', 'max:255'],
            'status' => ['nullable', Rule::in(['scheduled', 'in_progress', 'completed', 'canceled'])],
            'scheduled_date' => ['nullable', 'date'],
            'job_address' => ['nullable', 'string', 'max:255'],
            'quoted_price' => ['nullable', 'numeric', 'min:0'],
            'quoted_total_cents' => ['nullable', 'integer', 'min:0'],
            'notes' => ['nullable', 'string'],
        ];
    }
}
