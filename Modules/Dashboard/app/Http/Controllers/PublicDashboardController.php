<?php

namespace Modules\Dashboard\Http\Controllers;

use App\Http\Controllers\Controller;
use Inertia\Inertia;
use Inertia\Response;
use Modules\Dashboard\Http\Requests\PublicDashboardRequest;
use Modules\Dashboard\Services\PublicDashboardShareService;
use Modules\Dashboard\Services\PublicDashboardViewService;
use Modules\Dashboard\Support\DashboardPeriod;

class PublicDashboardController extends Controller
{
    public function show(
        PublicDashboardRequest $request,
        string $token,
        PublicDashboardShareService $share,
        PublicDashboardViewService $view,
    ): Response {
        $workspace = $share->resolveWorkspace($token);

        if ($workspace === null) {
            abort(404);
        }

        $period = DashboardPeriod::fromRequest($request);
        $assetId = $request->filled('asset_id') ? $request->integer('asset_id') : null;

        return Inertia::render(
            'Dashboard/Public',
            $view->build($workspace, $period, $assetId, $token),
        );
    }
}
