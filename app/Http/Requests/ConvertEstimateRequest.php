<?php

namespace App\Http\Requests;

use App\Services\CurrentBusinessResolver;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class ConvertEstimateRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $businessId = app(CurrentBusinessResolver::class)->id();

        return [
            'job_title' => ['nullable', 'string', 'max:255'],
            'scheduled_date' => ['nullable', 'date'],
            'assigned_user_id' => ['nullable', Rule::exists('business_user', 'user_id')->where('business_id', $businessId)->where('is_active', true)],
            'job_address' => ['nullable', 'string', 'max:255'],
            'job_notes' => ['nullable', 'string'],
            'create_invoice' => ['nullable', 'boolean'],
            'invoice_due_date' => ['nullable', 'date'],
            'invoice_discount' => ['nullable', 'numeric', 'min:0'],
            'invoice_tax_rate' => ['nullable', 'numeric', 'min:0', 'max:25'],
            'invoice_items' => ['required_if:create_invoice,1', 'array'],
            'invoice_items.*.description' => ['required_with:invoice_items', 'string', 'max:255'],
            'invoice_items.*.quantity' => ['required_with:invoice_items', 'numeric', 'gt:0'],
            'invoice_items.*.unit_price' => ['nullable', 'numeric', 'min:0'],
            'invoice_items.*.unit_price_cents' => ['nullable', 'integer', 'min:0'],
        ];
    }
}
