<?php

namespace Modules\Settings\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;
use Modules\Dashboard\Services\PublicDashboardShareService;
use Modules\Settings\Http\Requests\UpdateWorkspaceSettingsRequest;
use Modules\Workspaces\Models\Workspace;
use Modules\Workspaces\Support\WorkspaceFormOptions;

class WorkspaceSettingsController extends Controller
{
    public function edit(Request $request, Workspace $workspace, PublicDashboardShareService $publicDashboard): Response
    {
        $this->authorize('update', $workspace);

        return Inertia::render('Settings/Workspace', [
            'workspace' => [
                'id' => $workspace->id,
                'name' => $workspace->name,
                'industry_category' => $workspace->industry_category,
                'region' => $workspace->region,
                'timezone' => $workspace->timezone,
            ],
            'publicDashboard' => [
                'enabled' => $publicDashboard->isEnabled($workspace),
                'url' => $publicDashboard->url($workspace),
                'enabled_at' => $workspace->public_dashboard_enabled_at?->toIso8601String(),
            ],
            'timezones' => WorkspaceFormOptions::timezones(),
            'industryCategories' => WorkspaceFormOptions::industryCategories(),
            'regions' => WorkspaceFormOptions::regions(),
        ]);
    }

    public function update(UpdateWorkspaceSettingsRequest $request, Workspace $workspace): RedirectResponse
    {
        $workspace->update($request->validated());

        return back()->with('success', __('app.workspace_settings.saved'));
    }
}
