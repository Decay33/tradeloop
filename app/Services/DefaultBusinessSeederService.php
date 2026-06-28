<?php

namespace App\Services;

use App\Models\Business;
use App\Models\FollowupTemplate;
use App\Models\ServiceType;

class DefaultBusinessSeederService
{
    public function seed(Business $business): void
    {
        $serviceTypes = $this->seedServiceTypes($business);
        $templates = $this->seedTemplates($business);
        $this->seedRules($business, $serviceTypes, $templates);
    }

    public function seedServiceTypes(Business $business): array
    {
        $defaults = [
            ['General Handyman', 'Repairs', 45000, 12],
            ['Painting', 'Interior and Exterior', 180000, 24],
            ['Roofing', 'Exterior', 650000, 24],
            ['Landscaping', 'Outdoor', 85000, 6],
            ['Pressure Washing', 'Outdoor', 35000, 6],
            ['Asphalt / Sealcoating', 'Driveway', 120000, 11],
            ['Flooring', 'Interior', 280000, 24],
            ['Gutters', 'Exterior', 32500, 6],
            ['Fencing', 'Outdoor', 240000, 24],
            ['HVAC', 'Mechanical', 55000, 6],
            ['Deck Repair', 'Outdoor', 190000, 24],
            ['Driveway Work', 'Outdoor', 160000, 12],
            ['Window and Door', 'Exterior', 95000, 24],
            ['Garage Door', 'Exterior', 75000, 12],
            ['Remodeling', 'Interior', 950000, 24],
        ];

        return collect($defaults)->mapWithKeys(function (array $row) use ($business) {
            $service = ServiceType::updateOrCreate(
                ['business_id' => $business->id, 'name' => $row[0]],
                [
                    'category' => $row[1],
                    'default_price_cents' => $row[2],
                    'default_repeat_months' => $row[3],
                    'is_active' => true,
                ]
            );

            return [$service->name => $service];
        })->all();
    }

    public function seedTemplates(Business $business): array
    {
        $templates = [
            [
                'SMS Thank You',
                'sms',
                'thank_you',
                null,
                'Hi {{customer_first_name}}, thanks again for choosing {{business_name}} for your {{service_name}}. We appreciate your business.',
            ],
            [
                'SMS Review Request',
                'sms',
                'review_request',
                null,
                'Hi {{customer_first_name}}, thanks again for choosing {{business_name}} for your {{service_name}}. If you were happy with the work, would you mind leaving us a quick review? {{google_review_url}}',
            ],
            [
                'SMS Repeat Service Reminder',
                'sms',
                'repeat_service',
                null,
                'Hi {{customer_first_name}}, this is {{business_name}}. It has been a while since we completed your {{service_name}}. Would you like us to schedule a quick check-in or maintenance visit?',
            ],
            [
                'SMS Warranty Check',
                'sms',
                'warranty_check',
                null,
                'Hi {{customer_first_name}}, this is {{business_name}} checking in on your {{service_name}}. Is everything still looking good?',
            ],
            [
                'SMS Seasonal Reminder',
                'sms',
                'seasonal_reminder',
                null,
                'Hi {{customer_first_name}}, this is {{business_name}}. We are scheduling seasonal {{service_name}} visits now. Would you like to get on the calendar?',
            ],
            [
                'Email Review Request',
                'email',
                'review_request',
                'Thanks again from {{business_name}}',
                "Hi {{customer_first_name}},\n\nThank you again for choosing {{business_name}} for your {{service_name}}.\n\nIf you were happy with the work, would you mind leaving us a quick review? It really helps small businesses like ours.\n\nGoogle review link:\n{{google_review_url}}\n\nThanks,\n{{business_name}}\n{{business_phone}}",
            ],
        ];

        return collect($templates)->mapWithKeys(function (array $row) use ($business) {
            $template = FollowupTemplate::updateOrCreate(
                ['business_id' => $business->id, 'name' => $row[0]],
                [
                    'channel' => $row[1],
                    'purpose' => $row[2],
                    'subject' => $row[3],
                    'body' => $row[4],
                    'is_default' => true,
                ]
            );

            return [$template->purpose.'_'.$template->channel.'_'.$template->name => $template];
        })->all();
    }

    public function seedRules(Business $business, array $serviceTypes, array $templates): void
    {
        $byPurposeChannel = fn (string $purpose, string $channel) => collect($templates)
            ->first(fn (FollowupTemplate $template) => $template->purpose === $purpose && $template->channel === $channel);

        $rulesByService = [
            'General Handyman' => [['thank_you', 1, 'days'], ['review_request', 3, 'days'], ['warranty_check', 6, 'months'], ['repeat_service', 12, 'months']],
            'Asphalt / Sealcoating' => [['thank_you', 1, 'days'], ['review_request', 3, 'days'], ['repeat_service', 11, 'months'], ['repeat_service', 24, 'months']],
            'Painting' => [['thank_you', 1, 'days'], ['review_request', 4, 'days'], ['warranty_check', 12, 'months'], ['repeat_service', 24, 'months']],
            'Pressure Washing' => [['thank_you', 1, 'days'], ['review_request', 3, 'days'], ['repeat_service', 6, 'months'], ['seasonal_reminder', 12, 'months']],
            'Roofing' => [['thank_you', 1, 'days'], ['review_request', 5, 'days'], ['warranty_check', 11, 'months'], ['repeat_service', 24, 'months']],
            'Landscaping' => [['thank_you', 1, 'days'], ['review_request', 3, 'days'], ['seasonal_reminder', 3, 'months'], ['repeat_service', 6, 'months']],
            'Gutters' => [['thank_you', 1, 'days'], ['review_request', 3, 'days'], ['repeat_service', 6, 'months'], ['seasonal_reminder', 12, 'months']],
            'HVAC' => [['thank_you', 1, 'days'], ['review_request', 4, 'days'], ['repeat_service', 6, 'months'], ['seasonal_reminder', 12, 'months']],
            'Deck Repair' => [['thank_you', 1, 'days'], ['review_request', 3, 'days'], ['warranty_check', 12, 'months'], ['repeat_service', 24, 'months']],
            'Fencing' => [['thank_you', 1, 'days'], ['review_request', 3, 'days'], ['warranty_check', 12, 'months'], ['repeat_service', 24, 'months']],
        ];

        foreach ($serviceTypes as $service) {
            $serviceRules = $rulesByService[$service->name] ?? $rulesByService['General Handyman'];

            foreach ($serviceRules as $row) {
                [$purpose, $amount, $unit] = $row;
                $channel = $purpose === 'review_request' ? 'email' : 'sms';
                $template = $byPurposeChannel($purpose, $channel) ?: $byPurposeChannel($purpose, 'sms');

                if (! $template) {
                    continue;
                }

                $business->followupRules()->updateOrCreate(
                    [
                        'service_type_id' => $service->id,
                        'purpose' => $purpose,
                        'delay_amount' => $amount,
                        'delay_unit' => $unit,
                        'channel' => $template->channel,
                    ],
                    [
                        'template_id' => $template->id,
                        'trigger_event' => 'job_completed',
                        'is_active' => true,
                    ]
                );
            }
        }
    }
}
