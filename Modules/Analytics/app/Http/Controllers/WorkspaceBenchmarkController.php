<?php

namespace Modules\Analytics\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;
use Modules\Analytics\Services\WorkspaceBenchmarkService;
use Modules\Connections\Services\WorkspaceAssetScopeService;
use Modules\Workspaces\Models\Workspace;

class WorkspaceBenchmarkController extends Controller
{
    public function show(
        Request $request,
        Workspace $workspace,
        WorkspaceBenchmarkService $benchmarks,
        WorkspaceAssetScopeService $assetScope,
    ): Response {
        $this->authorize('view', $workspace);

        $request->validate([
            'asset_id' => ['sometimes', 'nullable', 'integer', 'min:1'],
        ]);

        $scope = $assetScope->resolve(
            $workspace,
            $request->filled('asset_id') ? $request->integer('asset_id') : null,
        );

        return Inertia::render('Analytics/Benchmarks', [
            'workspace' => $workspace->only(['id', 'name', 'industry_category']),
            'assetScope' => [
                'assets' => $assetScope->serializeForFrontend($scope['all']),
                'selected_asset_id' => $scope['selected_asset_id'],
                'route' => 'workspaces.benchmarks',
            ],
            'benchmarks' => $benchmarks->build($workspace, $scope['selected']),
            'hasConnectedAssets' => $scope['all']->isNotEmpty(),
        ]);
    }
}
