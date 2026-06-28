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
            'title' => ['required', 'string', 'max:255'],
            'status' => ['nullable', Rule::in(['scheduled', 'in_progress', 'completed', 'canceled'])],
            'scheduled_date' => ['nullable', 'date'],
            'job_address' => ['nullable', 'string', 'max:255'],
            'notes' => ['nullable', 'string'],
        ];
    }
}
