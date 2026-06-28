<?php

namespace Database\Factories;

use App\Models\Business;
use App\Models\Customer;
use App\Models\Job;
use App\Models\ServiceType;
use Illuminate\Database\Eloquent\Factories\Factory;

/** @extends Factory<Job> */
class JobFactory extends Factory
{
    protected $model = Job::class;

    public function definition(): array
    {
        return [
            'business_id' => Business::factory(),
            'customer_id' => Customer::factory(),
            'service_type_id' => ServiceType::factory(),
            'title' => fake()->sentence(4),
            'status' => 'scheduled',
            'scheduled_date' => now()->addWeek()->toDateString(),
            'job_address' => fake()->streetAddress(),
        ];
    }
}
