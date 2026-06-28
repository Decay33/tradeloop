<?php

namespace App\Http\Requests;

use App\Services\CurrentBusinessResolver;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class FollowupRuleRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $businessId = app(CurrentBusinessResolver::class)->id();

        return [
            'service_type_id' => ['required', Rule::exists('service_types', 'id')->where('business_id', $businessId)],
            'template_id' => ['required', Rule::exists('followup_templates', 'id')->where('business_id', $businessId)],
            'trigger_event' => ['required', Rule::in(['job_completed'])],
            'delay_amount' => ['required', 'integer', 'min:0', 'max:120'],
            'delay_unit' => ['required', Rule::in(['days', 'weeks', 'months'])],
            'channel' => ['required', Rule::in(['sms', 'email'])],
            'purpose' => ['required', Rule::in(['thank_you', 'review_request', 'repeat_service', 'warranty_check', 'seasonal_reminder', 'custom'])],
            'is_active' => ['nullable', 'boolean'],
        ];
    }
}
