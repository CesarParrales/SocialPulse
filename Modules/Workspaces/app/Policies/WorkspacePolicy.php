<?php

namespace Modules\Workspaces\Policies;

use App\Models\User;
use Modules\Workspaces\Models\Workspace;

class WorkspacePolicy
{
    public function viewAny(User $user): bool
    {
        return $user->isSuperAdmin()
            || $user->agency_id !== null
            || $user->workspaces()->exists();
    }

    public function view(User $user, Workspace $workspace): bool
    {
        return $user->canAccessWorkspace($workspace);
    }

    public function create(User $user): bool
    {
        return $user->isSuperAdmin() || $user->isAgencyAdmin();
    }

    public function assignMember(User $user, Workspace $workspace): bool
    {
        return $user->isSuperAdmin()
            || ($user->isAgencyAdmin() && $user->agency_id === $workspace->agency_id);
    }

    public function update(User $user, Workspace $workspace): bool
    {
        return $user->isSuperAdmin()
            || ($user->isAgencyAdmin() && $user->agency_id === $workspace->agency_id);
    }

    public function customizeDashboard(User $user, Workspace $workspace): bool
    {
        if (! $user->canAccessWorkspace($workspace)) {
            return false;
        }

        return ! $user->isClientReadonly();
    }

    public function managePublicDashboard(User $user, Workspace $workspace): bool
    {
        return $this->update($user, $workspace);
    }
}
