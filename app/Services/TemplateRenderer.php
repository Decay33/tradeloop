<?php

namespace App\Services;

use App\Models\Business;
use App\Models\Customer;
use App\Models\Job;

class TemplateRenderer
{
    public function render(?string $template, Business $business, Customer $customer, Job $job): string
    {
        if ($template === null) {
            return '';
        }

        $job->loadMissing('serviceType');

        $values = [
            '{{business_name}}' => $business->name ?? '',
            '{{customer_first_name}}' => $customer->first_name ?: $customer->display_name,
            '{{customer_full_name}}' => $customer->full_name ?: $customer->display_name,
            '{{service_name}}' => $job->serviceType?->name ?? '',
            '{{job_completed_date}}' => optional($job->completed_at)->timezone($business->timezone)->format('M j, Y') ?? '',
            '{{google_review_url}}' => $business->google_review_url ?? '',
            '{{facebook_review_url}}' => $business->facebook_review_url ?? '',
            '{{business_phone}}' => $business->phone ?? '',
            '{{business_email}}' => $business->email ?? '',
            '{{customer_company_name}}' => $customer->company_name ?? '',
            '{{job_title}}' => $job->title ?? '',
            '{{job_address}}' => $job->job_address ?? '',
        ];

        return str_replace(array_keys($values), array_values($values), $template);
    }
}
