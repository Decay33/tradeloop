<?php

use App\Http\Controllers\Auth\AuthenticatedSessionController;
use App\Http\Controllers\CustomerController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\EstimateController;
use App\Http\Controllers\FollowupController;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\InvoiceController;
use App\Http\Controllers\JobController;
use App\Http\Controllers\OnboardingController;
use App\Http\Controllers\ReportController;
use App\Http\Controllers\SettingsController;
use Illuminate\Support\Facades\Route;

Route::get('/', HomeController::class);

Route::middleware('guest')->group(function () {
    Route::get('/login', [AuthenticatedSessionController::class, 'create'])->name('login');
    Route::post('/login', [AuthenticatedSessionController::class, 'store']);
    Route::post('/demo-login', [AuthenticatedSessionController::class, 'demo'])->name('demo-login');
});

Route::post('/logout', [AuthenticatedSessionController::class, 'destroy'])->middleware('auth')->name('logout');

Route::middleware(['auth'])->group(function () {
    Route::get('/onboarding', [OnboardingController::class, 'index'])->name('onboarding.index');
    Route::post('/onboarding', [OnboardingController::class, 'store'])->name('onboarding.store');
});

Route::middleware(['auth', 'current.business'])->group(function () {
    Route::get('/dashboard', DashboardController::class)->name('dashboard');

    Route::resource('customers', CustomerController::class);
    Route::resource('jobs', JobController::class);
    Route::post('/jobs/{job}/start', [JobController::class, 'start'])->name('jobs.start');
    Route::post('/jobs/{job}/complete', [JobController::class, 'complete'])->name('jobs.complete');
    Route::post('/jobs/{job}/cancel', [JobController::class, 'cancel'])->name('jobs.cancel');

    Route::middleware('role:owner,manager')->group(function () {
        Route::resource('estimates', EstimateController::class);
        Route::post('/estimates/{estimate}/mark-sent', [EstimateController::class, 'markSent'])->name('estimates.mark-sent');
        Route::post('/estimates/{estimate}/accept', [EstimateController::class, 'accept'])->name('estimates.accept');
        Route::post('/estimates/{estimate}/decline', [EstimateController::class, 'decline'])->name('estimates.decline');
        Route::post('/estimates/{estimate}/create-job-and-invoice', [EstimateController::class, 'convert'])->name('estimates.convert');
        Route::get('/estimates/{estimate}/print', [EstimateController::class, 'print'])->name('estimates.print');

        Route::resource('invoices', InvoiceController::class);
        Route::post('/invoices/{invoice}/mark-sent', [InvoiceController::class, 'markSent'])->name('invoices.mark-sent');
        Route::post('/invoices/{invoice}/payments', [InvoiceController::class, 'recordPayment'])->name('invoices.payments.store');
        Route::post('/invoices/{invoice}/void', [InvoiceController::class, 'void'])->name('invoices.void');
        Route::get('/invoices/{invoice}/print', [InvoiceController::class, 'print'])->name('invoices.print');

        Route::get('/reports', ReportController::class)->name('reports');

        Route::get('/settings', [SettingsController::class, 'index'])->name('settings.index');
        Route::get('/settings/business', [SettingsController::class, 'business'])->name('settings.business');
        Route::match(['put', 'patch'], '/settings/business', [SettingsController::class, 'updateBusiness'])->name('settings.business.update');
        Route::get('/settings/service-types', [SettingsController::class, 'serviceTypes'])->name('settings.service-types');
        Route::post('/settings/service-types', [SettingsController::class, 'storeServiceType'])->name('settings.service-types.store');
        Route::match(['put', 'patch'], '/settings/service-types/{serviceType}', [SettingsController::class, 'updateServiceType'])->name('settings.service-types.update');
        Route::delete('/settings/service-types/{serviceType}', [SettingsController::class, 'destroyServiceType'])->name('settings.service-types.destroy');
        Route::get('/settings/message-templates', [SettingsController::class, 'messageTemplates'])->name('settings.message-templates');
        Route::post('/settings/message-templates', [SettingsController::class, 'storeMessageTemplate'])->name('settings.message-templates.store');
        Route::match(['put', 'patch'], '/settings/message-templates/{template}', [SettingsController::class, 'updateMessageTemplate'])->name('settings.message-templates.update');
        Route::delete('/settings/message-templates/{template}', [SettingsController::class, 'destroyMessageTemplate'])->name('settings.message-templates.destroy');
        Route::get('/settings/follow-up-rules', [SettingsController::class, 'followupRules'])->name('settings.follow-up-rules');
        Route::post('/settings/follow-up-rules', [SettingsController::class, 'storeFollowupRule'])->name('settings.follow-up-rules.store');
        Route::match(['put', 'patch'], '/settings/follow-up-rules/{rule}', [SettingsController::class, 'updateFollowupRule'])->name('settings.follow-up-rules.update');
        Route::delete('/settings/follow-up-rules/{rule}', [SettingsController::class, 'destroyFollowupRule'])->name('settings.follow-up-rules.destroy');
        Route::get('/settings/team', [SettingsController::class, 'team'])->name('settings.team');
    });

    Route::get('/follow-ups', [FollowupController::class, 'index'])->name('follow-ups.index');
    Route::get('/follow-ups/{followupMessage}', [FollowupController::class, 'show'])->name('follow-ups.show');
    Route::post('/follow-ups/{followupMessage}/send-now', [FollowupController::class, 'sendNow'])->name('follow-ups.send-now');
    Route::post('/follow-ups/{followupMessage}/cancel', [FollowupController::class, 'cancel'])->name('follow-ups.cancel');
    Route::post('/follow-ups/{followupMessage}/reschedule', [FollowupController::class, 'reschedule'])->name('follow-ups.reschedule');
});
