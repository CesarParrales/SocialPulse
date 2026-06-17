<?php

namespace Modules\Analytics\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Support\PeriodOptions;
use Inertia\Inertia;
use Inertia\Response;
use Modules\Analytics\Enums\ComparisonType;
use Modules\Analytics\Http\Requests\WorkspaceComparisonRequest;
use Modules\Analytics\Services\WorkspaceComparisonService;
use Modules\Analytics\Support\ComparisonContext;
use Modules\Connections\Services\WorkspaceAssetScopeService;
use Modules\Workspaces\Models\Workspace;

class WorkspaceComparisonController extends Controller
{
    public function show(
        WorkspaceComparisonRequest $request,
        Workspace $workspace,
        WorkspaceComparisonService $comparison,
        WorkspaceAssetScopeService $assetScope,
    ): Response {
        $context = ComparisonContext::fromRequest($request);
        $scope = $assetScope->resolve(
            $workspace,
            $request->filled('asset_id') ? $request->integer('asset_id') : null,
        );

        $comparisonData = $comparison->build($scope['selected'], $context);

        return Inertia::render('Analytics/Compare', [
            'workspace' => $workspace->only(['id', 'name']),
            'assetScope' => [
                'assets' => $assetScope->serializeForFrontend($scope['all']),
                'selected_asset_id' => $scope['selected_asset_id'],
                'route' => 'workspaces.compare',
            ],
            'filters' => array_merge($context->toFilters(), [
                'period' => $request->string('period', '30d')->value(),
                'asset_id' => $scope['selected_asset_id'],
            ]),
            'comparisonTypes' => collect(ComparisonType::cases())->map(fn (ComparisonType $type) => [
                'value' => $type->value,
                'label' => $type->label(),
            ])->all(),
            'periodOptions' => PeriodOptions::presets(),
            'comparison' => $comparisonData,
            'hasConnectedAssets' => $scope['all']->isNotEmpty(),
        ]);
    }
}
