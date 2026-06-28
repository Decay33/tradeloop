<?php

namespace Database\Seeders;

use App\Models\Business;
use App\Services\DefaultBusinessSeederService;
use Illuminate\Database\Seeder;

class FollowupRuleSeeder extends Seeder
{
    public function run(): void
    {
        Business::query()->each(function (Business $business) {
            $defaults = app(DefaultBusinessSeederService::class);
            $services = $business->serviceTypes()->get()->keyBy('name')->all();
            $templates = $business->followupTemplates()->get()->all();

            $defaults->seedRules($business, $services, $templates);
        });
    }
}
