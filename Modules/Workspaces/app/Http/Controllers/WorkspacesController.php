<?php

namespace Modules\Workspaces\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;
use Modules\Workspaces\Enums\SystemRole;
use Modules\Workspaces\Http\Requests\AssignWorkspaceMemberRequest;
use Modules\Workspaces\Http\Requests\StoreWorkspaceRequest;
use Modules\Workspaces\Models\Agency;
use Modules\Workspaces\Models\Workspace;

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

        return Inertia::render('Workspaces/Index', [
            'workspaces' => $workspaces,
            'canCreate' => $request->user()->can('create', Workspace::class),
        ]);
    }

    public function create(Request $request): Response
    {
        $this->authorize('create', Workspace::class);

        return Inertia::render('Workspaces/Create', [
            'timezones' => $this->timezoneOptions(),
            'industryCategories' => $this->industryCategories(),
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
            ->with('success', 'Workspace creado correctamente.');
    }

    public function show(Request $request, Workspace $workspace): Response
    {
        $this->authorize('view', $workspace);

        $workspace->load([
            'agency:id,name,plan',
            'members:id,name,email',
        ]);

        $assignableOperators = collect();

        if ($request->user()->can('assignMember', $workspace)) {
            $assignableOperators = User::query()
                ->where('agency_id', $workspace->agency_id)
                ->role(SystemRole::Operator->value)
                ->whereNotIn('id', $workspace->members->pluck('id'))
                ->orderBy('name')
                ->get(['id', 'name', 'email']);
        }

        return Inertia::render('Workspaces/Show', [
            'workspace' => $workspace,
            'canAssignMembers' => $request->user()->can('assignMember', $workspace),
            'assignableOperators' => $assignableOperators,
            'memberRoles' => [
                ['value' => 'operator', 'label' => 'Operador'],
                ['value' => 'client_readonly', 'label' => 'Cliente (solo lectura)'],
            ],
        ]);
    }

    public function assignMember(AssignWorkspaceMemberRequest $request, Workspace $workspace): RedirectResponse
    {
        $member = User::query()->where('email', $request->string('email'))->firstOrFail();

        if ($member->agency_id !== $workspace->agency_id) {
            return back()->withErrors(['email' => 'El usuario debe pertenecer a la misma agencia.']);
        }

        $workspace->members()->syncWithoutDetaching([
            $member->id => ['role' => $request->validated('role')],
        ]);

        return back()->with('success', 'Miembro asignado al workspace.');
    }

    /**
     * @return list<string>
     */
    private function timezoneOptions(): array
    {
        return [
            'America/Mexico_City',
            'America/Bogota',
            'America/Lima',
            'America/Guayaquil',
            'America/Santiago',
            'America/Buenos_Aires',
            'America/Sao_Paulo',
            'UTC',
        ];
    }

    /**
     * @return list<string>
     */
    private function industryCategories(): array
    {
        return [
            'Retail',
            'Salud',
            'Educación',
            'Tecnología',
            'Alimentos y bebidas',
            'Servicios profesionales',
            'Otro',
        ];
    }
}
