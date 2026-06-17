<?php

namespace Modules\Dashboard\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Support\PeriodOptions;
use Illuminate\Http\RedirectResponse;
use Inertia\Inertia;
use Inertia\Response;
use Modules\Connections\Services\WorkspaceAssetScopeService;
use Modules\Dashboard\Http\Requests\UpdateDashboardKpiPreferencesRequest;
use Modules\Dashboard\Http\Requests\WorkspaceDashboardRequest;
use Modules\Dashboard\Services\WorkspaceAnalyticsService;
use Modules\Dashboard\Services\WorkspaceDashboardService;
use Modules\Dashboard\Support\DashboardKpiPreferences;
use Modules\Dashboard\Support\DashboardPeriod;
use Modules\Workspaces\Models\Workspace;

class WorkspaceDashboardController extends Controller
{
    public function show(
        WorkspaceDashboardRequest $request,
        Workspace $workspace,
        WorkspaceDashboardService $dashboard,
        WorkspaceAnalyticsService $analytics,
        WorkspaceAssetScopeService $assetScope,
    ): Response {
        $period = DashboardPeriod::fromRequest($request);
        $scope = $assetScope->resolve(
            $workspace,
            $request->filled('asset_id') ? $request->integer('asset_id') : null,
        );

        $base = $dashboard->build($workspace, $period, $scope['selected']);
        $base['summary']['connected_assets'] = $scope['all']->count();
        $analyticsData = $analytics->build($scope['selected'], $period);

        return Inertia::render('Dashboard/Workspace', [
            'workspace' => $workspace->only(['id', 'name', 'timezone']),
            'periodOptions' => PeriodOptions::presets(includeCustom: true),
            'kpiPreferences' => [
                'visible_kpis' => DashboardKpiPreferences::visibleFromSettings($workspace->settings),
                'can_customize' => $request->user()?->can('customizeDashboard', $workspace) ?? false,
            ],
            'assetScope' => [
                'assets' => $assetScope->serializeForFrontend($scope['all']),
                'selected_asset_id' => $scope['selected_asset_id'],
                'route' => 'workspaces.dashboard',
            ],
            ...$base,
            'analytics' => $analyticsData,
        ]);
    }

    public function updateKpiPreferences(
        UpdateDashboardKpiPreferencesRequest $request,
        Workspace $workspace,
    ): RedirectResponse {
        $workspace->update([
            'settings' => DashboardKpiPreferences::mergeIntoSettings(
                $workspace->settings,
                $request->validated('visible_kpis'),
            ),
        ]);

        return back();
    }
}
