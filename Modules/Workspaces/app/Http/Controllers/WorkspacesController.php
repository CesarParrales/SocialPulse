<?php

namespace Modules\Workspaces\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;
use Inertia\Inertia;
use Inertia\Response;
use Modules\Connections\Enums\ConnectionStatus;
use Modules\Connections\Models\ConnectedAsset;
use Modules\Connections\Models\PlatformConnection;
use Modules\Dashboard\Services\WorkspaceOverviewService;
use Modules\Workspaces\Enums\SystemRole;
use Modules\Workspaces\Enums\WorkspaceMemberRole;
use Modules\Workspaces\Http\Requests\AssignWorkspaceMemberRequest;
use Modules\Workspaces\Http\Requests\StoreWorkspaceRequest;
use Modules\Workspaces\Mail\AgencyInvitationMail;
use Modules\Workspaces\Models\Agency;
use Modules\Workspaces\Models\AgencyInvitation;
use Modules\Workspaces\Models\Workspace;
use Modules\Workspaces\Support\WorkspaceFormOptions;

class WorkspacesController extends Controller
{
    public function index(Request $request): Response
    {
        $this->authorize('viewAny', Workspace::class);

        $workspaces = Workspace::query()
            ->accessibleBy($request->user())
            ->with('agency:id,name')
            ->orderBy('name')
            ->get(['id', 'agency_id', 'name', 'industry_category', 'timezone', 'created_at']);

        $countsByWorkspace = ConnectedAsset::query()
            ->join('platform_connections', 'connected_assets.connection_id', '=', 'platform_connections.id')
            ->where('connected_assets.is_active', true)
            ->whereIn('platform_connections.workspace_id', $workspaces->pluck('id'))
            ->groupBy('platform_connections.workspace_id')
            ->selectRaw('platform_connections.workspace_id, COUNT(*) as aggregate')
            ->pluck('aggregate', 'platform_connections.workspace_id');

        return Inertia::render('Workspaces/Index', [
            'workspaces' => $workspaces->map(fn (Workspace $workspace) => [
                'id' => $workspace->id,
                'name' => $workspace->name,
                'industry_category' => $workspace->industry_category,
                'timezone' => $workspace->timezone,
                'created_at' => $workspace->created_at?->toIso8601String(),
                'agency' => $workspace->agency,
                'connected_assets_count' => (int) ($countsByWorkspace[$workspace->id] ?? 0),
            ]),
            'canCreate' => $request->user()->can('create', Workspace::class),
            'isClientView' => $request->user()->isClientReadonly(),
        ]);
    }

    public function create(Request $request): Response
    {
        $this->authorize('create', Workspace::class);

        return Inertia::render('Workspaces/Create', [
            'timezones' => WorkspaceFormOptions::timezones(),
            'industryCategories' => WorkspaceFormOptions::industryCategories(),
            'regions' => WorkspaceFormOptions::regions(),
            'agencies' => $request->user()->isSuperAdmin()
                ? Agency::query()->orderBy('name')->get(['id', 'name'])
                : [],
        ]);
    }

    public function store(StoreWorkspaceRequest $request): RedirectResponse
    {
        $data = $request->validated();

        $agencyId = $request->user()->isSuperAdmin()
            ? (int) $data['agency_id']
            : (int) $request->user()->agency_id;

        unset($data['agency_id']);

        $workspace = Workspace::query()->create([
            ...$data,
            'agency_id' => $agencyId,
        ]);

        return redirect()
            ->route('workspaces.show', $workspace)
            ->with('success', __('app.workspaces.created'));
    }

    public function show(
        Request $request,
        Workspace $workspace,
        WorkspaceOverviewService $overview,
    ): Response {
        $this->authorize('view', $workspace);

        $workspace->load([
            'agency:id,name,plan',
            'members:id,name,email',
        ]);

        $assignableMembers = collect();

        if ($request->user()->can('assignMember', $workspace)) {
            $assignableMembers = User::query()
                ->where('agency_id', $workspace->agency_id)
                ->whereNotIn('id', $workspace->members->pluck('id'))
                ->whereDoesntHave('roles', fn ($query) => $query->where('name', SystemRole::SuperAdmin->value))
                ->orderBy('name')
                ->get(['id', 'name', 'email']);
        }

        $connectionsCount = PlatformConnection::query()
            ->where('workspace_id', $workspace->id)
            ->where('status', ConnectionStatus::Active)
            ->count();

        $connectedAssetsCount = ConnectedAsset::query()
            ->where('is_active', true)
            ->whereHas('connection', fn ($query) => $query->where('workspace_id', $workspace->id))
            ->count();

        $membersCount = $workspace->members->count();
        $canManageConnections = $request->user()->can('manage', [PlatformConnection::class, $workspace]);

        return Inertia::render('Workspaces/Show', [
            'workspace' => $workspace,
            'canAssignMembers' => $request->user()->can('assignMember', $workspace),
            'assignableMembers' => $assignableMembers,
            'stats' => [
                'connections_count' => $connectionsCount,
                'connected_assets_count' => $connectedAssetsCount,
                'members_count' => $membersCount,
            ],
            'setup' => [
                'complete' => $connectionsCount > 0 && $connectedAssetsCount > 0,
                'steps' => [
                    [
                        'key' => 'connect',
                        'done' => $connectionsCount > 0,
                        'href' => $canManageConnections
                            ? route('workspaces.connections.index', $workspace)
                            : null,
                    ],
                    [
                        'key' => 'assets',
                        'done' => $connectedAssetsCount > 0,
                        'href' => $canManageConnections
                            ? route('workspaces.connections.index', $workspace)
                            : null,
                    ],
                    [
                        'key' => 'dashboard',
                        'done' => $connectedAssetsCount > 0,
                        'href' => route('workspaces.dashboard', $workspace),
                    ],
                ],
            ],
            'memberRoles' => [
                ['value' => 'operator', 'label' => __('app.workspaces.member_role_operator')],
                ['value' => 'client_readonly', 'label' => __('app.workspaces.member_role_client')],
            ],
            'performanceSnapshot' => $overview->build($workspace),
        ]);
    }

    public function assignMember(AssignWorkspaceMemberRequest $request, Workspace $workspace): RedirectResponse
    {
        $email = strtolower($request->string('email')->value());
        $role = $request->validated('role');
        $member = User::query()->where('email', $email)->first();

        if ($member === null) {
            if (
                $request->boolean('invite')
                && $role === WorkspaceMemberRole::ClientReadonly->value
            ) {
                return $this->inviteClientToWorkspace($request, $workspace, $email);
            }

            return back()->withErrors([
                'email' => __('app.workspaces.member_not_found'),
            ]);
        }

        if ($member->agency_id !== $workspace->agency_id) {
            return back()->withErrors(['email' => __('app.flash.workspaces.member_agency_mismatch')]);
        }

        $workspace->members()->syncWithoutDetaching([
            $member->id => ['role' => $role],
        ]);

        if ($role === WorkspaceMemberRole::ClientReadonly->value) {
            $this->ensureClientReadonlySystemRole($member);
        }

        return back()->with('success', __('app.workspaces.member_assigned'));
    }

    private function inviteClientToWorkspace(
        Request $request,
        Workspace $workspace,
        string $email,
    ): RedirectResponse {
        if (User::query()->where('email', $email)->exists()) {
            return back()->withErrors([
                'email' => __('app.flash.team.email_exists'),
            ]);
        }

        $invitation = AgencyInvitation::createForAgency(
            $workspace->agency,
            $request->user(),
            $email,
            SystemRole::ClientReadonly,
            $workspace,
        );

        Mail::to($invitation->email)->send(new AgencyInvitationMail($invitation));

        return back()->with('success', __('app.workspaces.client_invited'));
    }

    private function ensureClientReadonlySystemRole(User $member): void
    {
        if ($member->isSuperAdmin() || $member->isAgencyAdmin() || $member->hasRole(SystemRole::Operator->value)) {
            return;
        }

        $member->syncRoles([SystemRole::ClientReadonly->value]);
    }
}
