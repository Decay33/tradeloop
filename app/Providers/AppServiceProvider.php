<?php

namespace App\Providers;

use App\Models\Customer;
use App\Models\Estimate;
use App\Models\FollowupMessage;
use App\Models\FollowupRule;
use App\Models\FollowupTemplate;
use App\Models\Invoice;
use App\Models\Job;
use App\Models\Payment;
use App\Models\ServiceType;
use App\Policies\BusinessOwnedPolicy;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\URL;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        $appUrl = rtrim((string) config('app.url'), '/');

        if (config('tradeloop.path_prefix') !== '' || str_starts_with($appUrl, 'https://')) {
            URL::forceRootUrl($appUrl);

            if (str_starts_with($appUrl, 'https://')) {
                URL::forceScheme('https');
            }
        }

        foreach ([Customer::class, ServiceType::class, Estimate::class, Invoice::class, Payment::class, Job::class, FollowupTemplate::class, FollowupRule::class, FollowupMessage::class] as $model) {
            Gate::policy($model, BusinessOwnedPolicy::class);
        }
    }
}
