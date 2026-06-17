<?php

namespace Modules\Settings\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;
use Modules\Settings\Services\PlatformIntegrationsService;
use Modules\Workspaces\Models\Agency;

class SettingsIndexController extends Controller
{
    public function __invoke(
        Request $request,
        PlatformIntegrationsService $integrations,
    ): Response|RedirectResponse {
        $user = $request->user();

        if ($user === null) {
            abort(403);
        }

        if ($user->isSuperAdmin() && $user->agency_id === null) {
            return redirect()->route('settings.platform.index', ['tab' => 'integrations']);
        }

        if (! $user->isSuperAdmin() && ! $user->isAgencyAdmin()) {
            abort(403);
        }

        $agency = Agency::query()->findOrFail($user->agency_id);
        $status = $integrations->status($agency->id);

        return Inertia::render('Settings/Index', [
            'canManagePlatform' => $user->isSuperAdmin(),
            'canManageTeam' => $user->isAgencyAdmin() || $user->isSuperAdmin(),
            'agency' => [
                'name' => $agency->name,
                'plan' => $agency->plan->value,
                'plan_label' => __('app.platform.plans.'.$agency->plan->value),
                'billing_email' => $agency->billing_email,
                'default_locale' => $agency->settings['default_locale'] ?? 'es',
            ],
            'integrations' => $status,
            'integrationsSummary' => $this->integrationsSummary($status),
        ]);
    }

    /**
     * @param  array<string, mixed>  $status
     * @return array{configured: int, total: int}
     */
    private function integrationsSummary(array $status): array
    {
        $flags = [
            $status['meta']['configured'] ?? false,
            $status['meta']['system_user_configured'] ?? false,
            $status['google']['configured'] ?? false,
            $status['tiktok']['configured'] ?? false,
            $status['linkedin']['configured'] ?? false,
            $status['youtube']['configured'] ?? false,
        ];

        return [
            'configured' => count(array_filter($flags)),
            'total' => count($flags),
        ];
    }
}
