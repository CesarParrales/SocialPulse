<?php

namespace Modules\Settings\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;
use Modules\Settings\Http\Requests\UpdateAgencySettingsRequest;
use Modules\Settings\Models\IntegrationCredentialSet;
use Modules\Settings\Services\PlatformIntegrationsService;
use Modules\Settings\Support\OAuthRedirectCatalog;
use Modules\Workspaces\Models\Agency;

class AgencySettingsController extends Controller
{
    public function edit(Request $request, PlatformIntegrationsService $integrations): Response
    {
        $this->authorizeSettings($request);

        $agency = $this->resolveAgency($request);
        $agencyCredentials = IntegrationCredentialSet::forAgency($agency->id);

        return Inertia::render('Settings/Agency', [
            'activeTab' => $request->query('tab') === 'integrations' ? 'integrations' : 'general',
            'agency' => [
                'id' => $agency->id,
                'name' => $agency->name,
                'plan' => $agency->plan->value,
                'billing_email' => $agency->billing_email,
                'default_locale' => $agency->settings['default_locale'] ?? 'es',
            ],
            'integrations' => $integrations->status($agency->id),
            'integrationCredentials' => $agencyCredentials->toFormPayload(),
            'oauthRedirects' => OAuthRedirectCatalog::payload(),
        ]);
    }

    public function update(UpdateAgencySettingsRequest $request): RedirectResponse
    {
        $agency = $this->resolveAgency($request);

        $settings = $agency->settings ?? [];
        $settings['default_locale'] = $request->string('default_locale')->value();

        $agency->update([
            'name' => $request->string('name')->value(),
            'billing_email' => $request->input('billing_email'),
            'settings' => $settings,
        ]);

        return back()->with('success', __('app.settings.saved'));
    }

    private function authorizeSettings(Request $request): void
    {
        $user = $request->user();

        if ($user === null || (! $user->isSuperAdmin() && ! $user->isAgencyAdmin())) {
            abort(403);
        }
    }

    private function resolveAgency(Request $request): Agency
    {
        $user = $request->user();

        if ($user?->isSuperAdmin() && $request->filled('agency_id')) {
            return Agency::query()->findOrFail($request->integer('agency_id'));
        }

        if ($user?->agency_id === null) {
            abort(403);
        }

        return Agency::query()->findOrFail($user->agency_id);
    }
}
