<?php

namespace App\Http\Controllers;

use App\Http\Requests\BusinessProfileRequest;
use App\Models\Business;
use App\Services\CurrentBusinessResolver;
use App\Services\DefaultBusinessSeederService;
use Inertia\Inertia;
use Inertia\Response;

class OnboardingController extends Controller
{
    public function index(): Response
    {
        return Inertia::render('Onboarding/Index');
    }

    public function store(BusinessProfileRequest $request, DefaultBusinessSeederService $defaults)
    {
        $business = Business::create($request->validated());
        $business->users()->attach($request->user()->id, ['role' => 'owner']);
        $defaults->seed($business);

        app(CurrentBusinessResolver::class)->set($business, $request->user());

        return redirect()->route('dashboard')->with('success', 'Business profile created.');
    }
}
