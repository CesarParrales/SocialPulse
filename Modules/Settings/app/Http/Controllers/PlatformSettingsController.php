<?php

namespace Modules\Settings\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;
use Inertia\Inertia;
use Inertia\Response;
use Modules\Settings\Http\Requests\StorePlatformAgencyRequest;
use Modules\Settings\Http\Requests\UpdatePlatformAgencyRequest;
use Modules\Settings\Models\IntegrationCredentialSet;
use Modules\Settings\Services\IntegrationEnvImporter;
use Modules\Settings\Services\PlatformIntegrationsService;
use Modules\Settings\Services\PlatformStatsService;
use Modules\Settings\Support\OAuthRedirectCatalog;
use Modules\Workspaces\Enums\AgencyPlan;
use Modules\Workspaces\Enums\SystemRole;
use Modules\Workspaces\Mail\AgencyInvitationMail;
use Modules\Workspaces\Models\Agency;
use Modules\Workspaces\Models\AgencyInvitation;

class PlatformSettingsController extends Controller
{
    public function index(
        Request $request,
        PlatformStatsService $stats,
        PlatformIntegrationsService $integrations,
    ): Response {
        $this->authorizeSuperAdmin($request);

        $agencies = Agency::query()
            ->withCount(['workspaces', 'users'])
            ->orderBy('name')
            ->get(['id', 'name', 'plan', 'billing_email', 'created_at']);

        return Inertia::render('Settings/Platform', [
            'activeTab' => match ($request->query('tab')) {
                'integrations' => 'integrations',
                'agencies' => 'agencies',
                default => 'overview',
            },
            'stats' => $stats->summary(),
            'integrations' => $integrations->status(),
            'integrationCredentials' => IntegrationCredentialSet::platform()->toFormPayload(),
            'oauthRedirects' => OAuthRedirectCatalog::payload(),
            'envImport' => $this->envImportPayload(
                IntegrationCredentialSet::platform(),
                route('settings.platform.integrations.import-env'),
            ),
            'agencies' => $agencies->map(fn (Agency $agency) => [
                'id' => $agency->id,
                'name' => $agency->name,
                'plan' => $agency->plan->value,
                'plan_label' => $agency->plan->label(),
                'billing_email' => $agency->billing_email,
                'workspaces_count' => $agency->workspaces_count,
                'users_count' => $agency->users_count,
                'created_at' => $agency->created_at?->toDateString(),
            ]),
            'planOptions' => collect(AgencyPlan::cases())->map(fn (AgencyPlan $plan) => [
                'value' => $plan->value,
                'label' => $plan->label(),
            ])->all(),
        ]);
    }

    public function createAgency(Request $request): Response
    {
        $this->authorizeSuperAdmin($request);

        return Inertia::render('Settings/PlatformCreateAgency', [
            'planOptions' => collect(AgencyPlan::cases())->map(fn (AgencyPlan $plan) => [
                'value' => $plan->value,
                'label' => $plan->label(),
            ])->all(),
        ]);
    }

    public function storeAgency(StorePlatformAgencyRequest $request): RedirectResponse
    {
        $validated = $request->validated();
        $adminEmail = $validated['admin_email'] ?? null;
        unset($validated['admin_email']);

        $agency = Agency::query()->create($validated);

        if ($adminEmail !== null && $adminEmail !== '') {
            $invitation = AgencyInvitation::createForAgency(
                $agency,
                $request->user(),
                $adminEmail,
                SystemRole::AgencyAdmin,
            );

            Mail::to($invitation->email)->send(new AgencyInvitationMail($invitation));

            return redirect()
                ->route('settings.platform.index')
                ->with('success', __('app.platform.agency_created_with_invite'));
        }

        return redirect()
            ->route('settings.platform.index')
            ->with('success', __('app.platform.agency_created'));
    }

    public function editAgency(
        Request $request,
        Agency $agency,
        PlatformIntegrationsService $integrations,
    ): Response {
        $this->authorizeSuperAdmin($request);

        $agency->loadCount(['workspaces', 'users']);

        return Inertia::render('Settings/PlatformAgency', [
            'activeTab' => $request->query('tab') === 'integrations' ? 'integrations' : 'general',
            'agency' => [
                'id' => $agency->id,
                'name' => $agency->name,
                'plan' => $agency->plan->value,
                'billing_email' => $agency->billing_email,
                'workspaces_count' => $agency->workspaces_count,
                'users_count' => $agency->users_count,
            ],
            'integrations' => $integrations->status($agency->id),
            'integrationCredentials' => IntegrationCredentialSet::forAgency($agency->id)->toFormPayload(),
            'oauthRedirects' => OAuthRedirectCatalog::payload(),
            'envImport' => $this->envImportPayload(
                IntegrationCredentialSet::forAgency($agency->id),
                route('settings.platform.agencies.integrations.import-env', $agency),
            ),
            'planOptions' => collect(AgencyPlan::cases())->map(fn (AgencyPlan $plan) => [
                'value' => $plan->value,
                'label' => $plan->label(),
            ])->all(),
        ]);
    }

    public function updateAgency(UpdatePlatformAgencyRequest $request, Agency $agency): RedirectResponse
    {
        $agency->update($request->validated());

        return redirect()
            ->route('settings.platform.agencies.edit', $agency)
            ->with('success', __('app.platform.agency_updated'));
    }

    private function authorizeSuperAdmin(Request $request): void
    {
        if ($request->user()?->isSuperAdmin() !== true) {
            abort(403);
        }
    }

    /**
     * @return array{canImport: bool, pendingCount: int, importRoute: string}
     */
    private function envImportPayload(
        IntegrationCredentialSet $credentials,
        string $importRoute,
    ): array {
        $importer = app(IntegrationEnvImporter::class);

        return [
            'canImport' => $importer->hasEnvValues(),
            'pendingCount' => count($importer->pendingEnvFields($credentials, false)),
            'importRoute' => $importRoute,
        ];
    }
}
