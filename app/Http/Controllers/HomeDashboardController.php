<?php

namespace App\Http\Controllers;

use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;
use Modules\Connections\Enums\ConnectionStatus;
use Modules\Connections\Models\ConnectedAsset;
use Modules\Connections\Models\PlatformConnection;
use Modules\Workspaces\Models\Workspace;

class HomeDashboardController extends Controller
{
    public function __invoke(Request $request): Response|RedirectResponse
    {
        $user = $request->user();

        if ($user->isClientReadonly()) {
            return redirect()->to($user->clientHomeUrl());
        }

        $accessibleWorkspaceIds = Workspace::query()
            ->accessibleBy($user)
            ->pluck('id');

        $workspaces = Workspace::query()
            ->accessibleBy($user)
            ->with('agency:id,name')
            ->orderBy('name')
            ->limit(6)
            ->get(['id', 'agency_id', 'name', 'industry_category', 'timezone']);

        $firstWorkspace = Workspace::query()
            ->accessibleBy($user)
            ->orderBy('name')
            ->first(['id']);

        $hasWorkspace = $accessibleWorkspaceIds->isNotEmpty();
        $hasConnection = $hasWorkspace && PlatformConnection::query()
            ->whereIn('workspace_id', $accessibleWorkspaceIds)
            ->where('status', ConnectionStatus::Active)
            ->exists();
        $hasActiveAssets = $hasWorkspace && ConnectedAsset::query()
            ->where('is_active', true)
            ->whereHas('connection', fn ($query) => $query->whereIn('workspace_id', $accessibleWorkspaceIds))
            ->exists();

        return Inertia::render('Dashboard', [
            'workspaces' => $workspaces,
            'canCreateWorkspace' => $user->can('create', Workspace::class),
            'onboarding' => [
                'complete' => $hasWorkspace && $hasConnection && $hasActiveAssets,
                'steps' => [
                    [
                        'key' => 'workspace',
                        'done' => $hasWorkspace,
                        'href' => $hasWorkspace && $firstWorkspace !== null
                            ? route('workspaces.show', $firstWorkspace)
                            : ($user->can('create', Workspace::class)
                                ? route('workspaces.create')
                                : route('workspaces.index')),
                    ],
                    [
                        'key' => 'connect',
                        'done' => $hasConnection,
                        'href' => $firstWorkspace !== null
                            ? route('workspaces.connections.index', $firstWorkspace)
                            : null,
                    ],
                    [
                        'key' => 'assets',
                        'done' => $hasActiveAssets,
                        'href' => $firstWorkspace !== null
                            ? route('workspaces.connections.index', $firstWorkspace)
                            : null,
                    ],
                    [
                        'key' => 'dashboard',
                        'done' => $hasActiveAssets,
                        'href' => $firstWorkspace !== null
                            ? route('workspaces.dashboard', $firstWorkspace)
                            : null,
                    ],
                ],
            ],
        ]);
    }
}
