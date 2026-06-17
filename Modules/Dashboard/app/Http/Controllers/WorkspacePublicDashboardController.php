<?php

namespace Modules\Dashboard\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Modules\Dashboard\Services\PublicDashboardShareService;
use Modules\Workspaces\Models\Workspace;

class WorkspacePublicDashboardController extends Controller
{
    public function enable(Request $request, Workspace $workspace, PublicDashboardShareService $share): RedirectResponse
    {
        $this->authorize('managePublicDashboard', $workspace);

        $share->enable($workspace);

        return back()->with('success', __('app.public_dashboard.enabled'));
    }

    public function disable(Request $request, Workspace $workspace, PublicDashboardShareService $share): RedirectResponse
    {
        $this->authorize('managePublicDashboard', $workspace);

        $share->disable($workspace);

        return back()->with('success', __('app.public_dashboard.disabled'));
    }

    public function regenerate(Request $request, Workspace $workspace, PublicDashboardShareService $share): RedirectResponse
    {
        $this->authorize('managePublicDashboard', $workspace);

        $share->regenerate($workspace);

        return back()->with('success', __('app.public_dashboard.regenerated'));
    }
}
