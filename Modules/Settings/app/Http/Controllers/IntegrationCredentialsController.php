<?php

namespace Modules\Settings\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Modules\Settings\Http\Requests\UpdateIntegrationCredentialsRequest;
use Modules\Settings\Models\IntegrationCredentialSet;
use Modules\Settings\Services\IntegrationEnvImporter;
use Modules\Workspaces\Models\Agency;

class IntegrationCredentialsController extends Controller
{
    public function updatePlatform(UpdateIntegrationCredentialsRequest $request): RedirectResponse
    {
        if ($request->user()?->isSuperAdmin() !== true) {
            abort(403);
        }

        $this->persistCredentials(IntegrationCredentialSet::platform(), $request);

        return back()->with('success', __('app.settings.integrations_saved'));
    }

    public function importPlatformFromEnv(Request $request, IntegrationEnvImporter $importer): RedirectResponse
    {
        if ($request->user()?->isSuperAdmin() !== true) {
            abort(403);
        }

        if (! $importer->hasEnvValues()) {
            return back()->with('success', __('app.settings.integrations_import_none'));
        }

        return $this->importFromEnvResponse(
            $importer->importPlatform($request->boolean('force')),
        );
    }

    public function importAgencyFromEnv(
        Request $request,
        Agency $agency,
        IntegrationEnvImporter $importer,
    ): RedirectResponse {
        if ($request->user()?->isSuperAdmin() !== true) {
            abort(403);
        }

        if (! $importer->hasEnvValues()) {
            return back()->with('success', __('app.settings.integrations_import_none'));
        }

        return $this->importFromEnvResponse(
            $importer->importAgency($agency->id, $request->boolean('force')),
        );
    }

    public function updateAgencyFromPlatform(
        UpdateIntegrationCredentialsRequest $request,
        Agency $agency,
    ): RedirectResponse {
        if ($request->user()?->isSuperAdmin() !== true) {
            abort(403);
        }

        $this->persistCredentials(IntegrationCredentialSet::forAgency($agency->id), $request);

        return back()->with('success', __('app.settings.integrations_saved'));
    }

    public function updateAgency(UpdateIntegrationCredentialsRequest $request): RedirectResponse
    {
        $user = $request->user();

        if ($user === null || (! $user->isSuperAdmin() && ! $user->isAgencyAdmin())) {
            abort(403);
        }

        $agency = $this->resolveAgency($request);
        $this->persistCredentials(IntegrationCredentialSet::forAgency($agency->id), $request);

        return back()->with('success', __('app.settings.integrations_saved'));
    }

    private function persistCredentials(
        IntegrationCredentialSet $credentials,
        UpdateIntegrationCredentialsRequest $request,
    ): void {
        $validated = $request->validated();

        foreach ([
            'meta_app_id',
            'meta_api_version',
            'meta_system_user_id',
            'meta_business_id',
            'google_client_id',
            'tiktok_client_key',
            'linkedin_client_id',
            'youtube_client_id',
        ] as $field) {
            if (array_key_exists($field, $validated)) {
                $credentials->{$field} = $validated[$field] !== '' ? $validated[$field] : null;
            }
        }

        foreach ([
            'meta_app_secret',
            'meta_system_user_access_token',
            'google_client_secret',
            'google_developer_token',
            'tiktok_client_secret',
            'linkedin_client_secret',
            'youtube_client_secret',
        ] as $secretField) {
            if ($request->filled($secretField)) {
                $credentials->{$secretField} = $validated[$secretField];
            }
        }

        $credentials->save();
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

    /**
     * @param  array{imported: list<string>, skipped: list<string>}  $result
     */
    private function importFromEnvResponse(array $result): RedirectResponse
    {
        if ($result['imported'] === []) {
            return back()->with('success', __('app.settings.integrations_import_none'));
        }

        return back()->with(
            'success',
            __('app.settings.integrations_imported', [
                'count' => count($result['imported']),
            ]),
        );
    }
}
