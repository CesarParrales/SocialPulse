<?php

namespace Modules\Workspaces\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;
use Inertia\Inertia;
use Inertia\Response;
use Modules\Workspaces\Enums\SystemRole;
use Modules\Workspaces\Http\Requests\StoreAgencyInvitationRequest;
use Modules\Workspaces\Mail\AgencyInvitationMail;
use Modules\Workspaces\Models\Agency;
use Modules\Workspaces\Models\AgencyInvitation;

class TeamController extends Controller
{
    public function index(Request $request): Response
    {
        $this->authorizeTeamManagement($request);

        $agency = $this->resolveAgency($request);

        $members = User::query()
            ->where('agency_id', $agency->id)
            ->with('roles')
            ->orderBy('name')
            ->get(['id', 'name', 'email']);

        $invitations = AgencyInvitation::query()
            ->where('agency_id', $agency->id)
            ->whereNull('accepted_at')
            ->where('expires_at', '>', now())
            ->orderByDesc('created_at')
            ->get(['id', 'email', 'role', 'expires_at', 'created_at']);

        return Inertia::render('Team/Index', [
            'agency' => $agency->only(['id', 'name']),
            'members' => $members->map(fn (User $user) => [
                'id' => $user->id,
                'name' => $user->name,
                'email' => $user->email,
                'roles' => $user->getRoleNames()->values()->all(),
            ]),
            'invitations' => $invitations,
            'invitableRoles' => [
                ['value' => SystemRole::AgencyAdmin->value, 'label' => __('app.flash.team.role_agency_admin')],
                ['value' => SystemRole::Operator->value, 'label' => __('app.workspaces.member_role_operator')],
                ['value' => SystemRole::ClientReadonly->value, 'label' => __('app.workspaces.member_role_client')],
            ],
            'clientInviteHint' => __('app.flash.team.client_invite_hint'),
        ]);
    }

    public function storeInvitation(StoreAgencyInvitationRequest $request): RedirectResponse
    {
        $agency = $this->resolveAgency($request);

        if (User::query()->where('email', $request->string('email'))->exists()) {
            return back()->withErrors([
                'email' => __('app.flash.team.email_exists'),
            ]);
        }

        $invitation = AgencyInvitation::createForAgency(
            $agency,
            $request->user(),
            $request->string('email')->value(),
            SystemRole::from($request->string('role')->value()),
        );

        Mail::to($invitation->email)->send(new AgencyInvitationMail($invitation));

        return back()->with('success', __('app.flash.team.invitation_sent'));
    }

    public function destroyInvitation(Request $request, AgencyInvitation $invitation): RedirectResponse
    {
        $this->authorizeTeamManagement($request);

        $agency = $this->resolveAgency($request);

        if ($invitation->agency_id !== $agency->id) {
            abort(403);
        }

        $invitation->delete();

        return back()->with('success', __('app.flash.team.invitation_cancelled'));
    }

    private function authorizeTeamManagement(Request $request): void
    {
        $user = $request->user();

        if ($user === null || (! $user->isSuperAdmin() && ! $user->isAgencyAdmin())) {
            abort(403);
        }
    }

    private function resolveAgency(Request $request): Agency
    {
        $user = $request->user();

        if ($user->isSuperAdmin() && $request->filled('agency_id')) {
            return Agency::query()->findOrFail($request->integer('agency_id'));
        }

        if ($user->agency_id === null) {
            abort(403, 'No perteneces a una agencia.');
        }

        return Agency::query()->findOrFail($user->agency_id);
    }
}
