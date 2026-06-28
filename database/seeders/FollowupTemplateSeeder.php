<?php

namespace Database\Seeders;

use App\Models\Business;
use App\Services\DefaultBusinessSeederService;
use Illuminate\Database\Seeder;

class FollowupTemplateSeeder extends Seeder
{
    public function run(): void
    {
        Business::query()->each(fn (Business $business) => app(DefaultBusinessSeederService::class)->seedTemplates($business));
    }
}
