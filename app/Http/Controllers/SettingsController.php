<?php

namespace App\Http\Controllers;

use App\Http\Requests\BusinessProfileRequest;
use App\Http\Requests\FollowupRuleRequest;
use App\Http\Requests\FollowupTemplateRequest;
use App\Http\Requests\ServiceTypeRequest;
use App\Http\Requests\TeamMemberRequest;
use App\Models\FollowupRule;
use App\Models\FollowupTemplate;
use App\Models\ServiceType;
use App\Models\User;
use App\Services\AuditLogger;
use App\Services\CurrentBusinessResolver;
use App\Support\Money;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Inertia\Inertia;
use Inertia\Response;

class SettingsController extends Controller
{
    public function index(): Response
    {
        return Inertia::render('Settings/Index');
    }

    public function business(): Response
    {
        return Inertia::render('Settings/Business', [
            'business' => app(CurrentBusinessResolver::class)->resolve(),
        ]);
    }

    public function updateBusiness(BusinessProfileRequest $request, AuditLogger $auditLogger)
    {
        $business = app(CurrentBusinessResolver::class)->resolve();
        $business->update($request->validated());
        $auditLogger->log('settings_updated', $business, $business, ['section' => 'business']);

        return back()->with('success', 'Business profile updated.');
    }

    public function serviceTypes(): Response
    {
        $business = app(CurrentBusinessResolver::class)->resolve();

        return Inertia::render('Settings/ServiceTypes', [
            'serviceTypes' => $business->serviceTypes()->orderBy('name')->get(),
        ]);
    }

    public function storeServiceType(ServiceTypeRequest $request)
    {
        $business = app(CurrentBusinessResolver::class)->resolve();
        $business->serviceTypes()->create([
            ...$request->safe()->except('default_price'),
            'default_price_cents' => Money::fromInput($request->input('default_price', 0)),
            'is_active' => $request->boolean('is_active', true),
        ]);

        return back()->with('success', 'Service type created.');
    }

    public function updateServiceType(ServiceTypeRequest $request, ServiceType $serviceType)
    {
        $this->authorize('update', $serviceType);
        $serviceType->update([
            ...$request->safe()->except('default_price'),
            'default_price_cents' => Money::fromInput($request->input('default_price', 0)),
            'is_active' => $request->boolean('is_active'),
        ]);

        return back()->with('success', 'Service type updated.');
    }

    public function destroyServiceType(ServiceType $serviceType)
    {
        $this->authorize('delete', $serviceType);
        $serviceType->delete();

        return back()->with('success', 'Service type deleted.');
    }

    public function messageTemplates(): Response
    {
        $business = app(CurrentBusinessResolver::class)->resolve();

        return Inertia::render('Settings/MessageTemplates', [
            'templates' => $business->followupTemplates()->orderBy('purpose')->orderBy('channel')->get(),
        ]);
    }

    public function storeMessageTemplate(FollowupTemplateRequest $request)
    {
        $business = app(CurrentBusinessResolver::class)->resolve();
        $business->followupTemplates()->create([
            ...$request->validated(),
            'is_default' => $request->boolean('is_default'),
        ]);

        return back()->with('success', 'Message template created.');
    }

    public function updateMessageTemplate(FollowupTemplateRequest $request, FollowupTemplate $template)
    {
        $this->authorize('update', $template);
        $template->update([
            ...$request->validated(),
            'is_default' => $request->boolean('is_default'),
        ]);

        return back()->with('success', 'Message template updated.');
    }

    public function destroyMessageTemplate(FollowupTemplate $template)
    {
        $this->authorize('delete', $template);
        $template->delete();

        return back()->with('success', 'Message template deleted.');
    }

    public function followupRules(): Response
    {
        $business = app(CurrentBusinessResolver::class)->resolve();

        return Inertia::render('Settings/FollowupRules', [
            'rules' => $business->followupRules()->with('serviceType', 'template')->orderBy('service_type_id')->orderBy('delay_amount')->get(),
            'serviceTypes' => $business->serviceTypes()->where('is_active', true)->orderBy('name')->get(),
            'templates' => $business->followupTemplates()->orderBy('purpose')->get(),
        ]);
    }

    public function storeFollowupRule(FollowupRuleRequest $request)
    {
        $business = app(CurrentBusinessResolver::class)->resolve();
        $business->followupRules()->create([
            ...$request->validated(),
            'is_active' => $request->boolean('is_active', true),
        ]);

        return back()->with('success', 'Follow-up rule created.');
    }

    public function updateFollowupRule(FollowupRuleRequest $request, FollowupRule $rule)
    {
        $this->authorize('update', $rule);
        $rule->update([
            ...$request->validated(),
            'is_active' => $request->boolean('is_active'),
        ]);

        return back()->with('success', 'Follow-up rule updated.');
    }

    public function destroyFollowupRule(FollowupRule $rule)
    {
        $this->authorize('delete', $rule);
        $rule->delete();

        return back()->with('success', 'Follow-up rule deleted.');
    }

    public function team(): Response
    {
        $business = app(CurrentBusinessResolver::class)->resolve();

        return Inertia::render('Settings/Team', [
            'users' => $business->users()->orderBy('name')->get(),
            'permissions' => User::allPermissions(),
        ]);
    }

    public function storeTeamMember(TeamMemberRequest $request, AuditLogger $auditLogger)
    {
        $business = app(CurrentBusinessResolver::class)->resolve();
        $user = User::create([
            'name' => $request->input('name'),
            'email' => $request->input('email'),
            'phone' => $request->input('phone'),
            'password' => Hash::make($request->input('temporary_password')),
            'email_verified_at' => now(),
        ]);

        $business->users()->attach($user->id, $this->pivotData($request));
        $auditLogger->log('team_member_created', $business, $user);

        return back()->with('success', 'Team member added.');
    }

    public function updateTeamMember(TeamMemberRequest $request, User $user, AuditLogger $auditLogger)
    {
        $business = app(CurrentBusinessResolver::class)->resolve();
        abort_unless($business->users()->whereKey($user->id)->exists(), 403);

        $user->update([
            'name' => $request->input('name'),
            'email' => $request->input('email'),
            'phone' => $request->input('phone'),
            ...($request->filled('temporary_password') ? ['password' => Hash::make($request->input('temporary_password'))] : []),
        ]);

        $business->users()->updateExistingPivot($user->id, $this->pivotData($request));
        $auditLogger->log('team_member_updated', $business, $user);

        return back()->with('success', 'Team member updated.');
    }

    public function deactivateTeamMember(User $user, AuditLogger $auditLogger)
    {
        $business = app(CurrentBusinessResolver::class)->resolve();
        abort_unless($business->users()->whereKey($user->id)->exists(), 403);
        abort_if($user->id === Auth::id(), 422, 'You cannot deactivate yourself.');

        $business->users()->updateExistingPivot($user->id, ['is_active' => false]);
        $auditLogger->log('team_member_deactivated', $business, $user);

        return back()->with('success', 'Team member deactivated.');
    }

    private function pivotData(TeamMemberRequest $request): array
    {
        $role = $request->input('role');

        return [
            'role' => $role,
            'permissions' => $role === 'custom' ? json_encode($request->input('permissions', [])) : null,
            'is_active' => $request->boolean('is_active', true),
        ];
    }
}
