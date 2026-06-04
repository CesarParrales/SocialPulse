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
}
