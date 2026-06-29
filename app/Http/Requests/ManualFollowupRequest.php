<?php

namespace App\Http\Requests;

use App\Services\CurrentBusinessResolver;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class ManualFollowupRequest extends FormRequest
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
            'estimate_id' => ['nullable', Rule::exists('estimates', 'id')->where('business_id', $businessId)],
            'job_id' => ['nullable', Rule::exists('jobs', 'id')->where('business_id', $businessId)],
            'channel' => ['required', Rule::in(['sms', 'email'])],
            'purpose' => ['required', Rule::in(['thank_you', 'review_request', 'repeat_service', 'seasonal_reminder', 'warranty_check', 'sales_follow_up'])],
            'scheduled_at' => ['required', 'date'],
            'subject' => ['nullable', 'string', 'max:255'],
            'body' => ['required', 'string'],
        ];
    }
}
