<?php

namespace Modules\Workspaces\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Auth\Events\Registered;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules;
use Inertia\Inertia;
use Inertia\Response;
use Modules\Workspaces\Enums\WorkspaceMemberRole;
use Modules\Workspaces\Models\AgencyInvitation;

class InvitationAcceptController extends Controller
{
    public function show(string $token): Response|RedirectResponse
    {
        $invitation = $this->findPendingInvitation($token);

        if ($invitation === null) {
            return redirect()->route('login')->withErrors([
                'email' => __('app.invitations.invalid_or_expired'),
            ]);
        }

        return Inertia::render('Auth/AcceptInvitation', [
            'email' => $invitation->email,
            'agencyName' => $invitation->agency->name,
            'role' => $invitation->role->value,
            'token' => $token,
        ]);
    }

    public function store(Request $request, string $token): RedirectResponse
    {
        $invitation = $this->findPendingInvitation($token);

        if ($invitation === null) {
            return redirect()->route('login')->withErrors([
                'email' => __('app.invitations.invalid_or_expired'),
            ]);
        }

        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'password' => ['required', 'confirmed', Rules\Password::defaults()],
        ]);

        $user = User::query()->create([
            'name' => $validated['name'],
            'email' => $invitation->email,
            'password' => Hash::make($validated['password']),
            'agency_id' => $invitation->agency_id,
            'email_verified_at' => now(),
        ]);

        $user->assignRole($invitation->role->value);

        if ($invitation->workspace_id !== null) {
            $user->workspaces()->syncWithoutDetaching([
                $invitation->workspace_id => [
                    'role' => WorkspaceMemberRole::ClientReadonly->value,
                ],
            ]);
        }

        $invitation->update(['accepted_at' => now()]);

        event(new Registered($user));

        Auth::login($user);

        if ($user->isClientReadonly()) {
            return redirect()->to($user->clientHomeUrl());
        }

        return redirect()->route('dashboard');
    }

    private function findPendingInvitation(string $token): ?AgencyInvitation
    {
        $invitation = AgencyInvitation::query()
            ->with('agency:id,name')
            ->where('token', $token)
            ->first();

        if ($invitation === null || ! $invitation->isPending()) {
            return null;
        }

        return $invitation;
    }
}
