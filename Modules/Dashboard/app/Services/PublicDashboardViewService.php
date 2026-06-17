<?php

namespace Modules\Dashboard\Services;

use App\Support\PeriodOptions;
use Modules\Connections\Services\WorkspaceAssetScopeService;
use Modules\Dashboard\Support\DashboardKpiPreferences;
use Modules\Dashboard\Support\DashboardPeriod;
use Modules\Workspaces\Models\Workspace;

class PublicDashboardViewService
{
    public function __construct(
        private readonly WorkspaceDashboardService $dashboard,
        private readonly WorkspaceAnalyticsService $analytics,
        private readonly WorkspaceAssetScopeService $assetScope,
    ) {}

    /**
     * @return array<string, mixed>
     */
    public function build(Workspace $workspace, DashboardPeriod $period, ?int $assetId, string $token): array
    {
        $scope = $this->assetScope->resolve($workspace, $assetId);
        $base = $this->dashboard->build($workspace, $period, $scope['selected']);
        $base['summary']['connected_assets'] = $scope['all']->count();
        unset($base['ingestionHealth']);

        $analyticsData = $this->analytics->build($scope['selected'], $period);

        return [
            'shareToken' => $token,
            'workspace' => $workspace->only(['name', 'timezone']),
            'periodOptions' => PeriodOptions::presets(includeCustom: true),
            'kpiPreferences' => [
                'visible_kpis' => DashboardKpiPreferences::visibleFromSettings($workspace->settings),
                'can_customize' => false,
            ],
            'assetScope' => [
                'assets' => $this->assetScope->serializeForFrontend($scope['all']),
                'selected_asset_id' => $scope['selected_asset_id'],
                'route' => 'public.dashboard',
                'routeParams' => ['token' => $token],
            ],
            ...$base,
            'analytics' => $analyticsData,
        ];
    }
}
