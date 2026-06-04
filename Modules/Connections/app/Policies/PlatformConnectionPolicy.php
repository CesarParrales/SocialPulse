<?php

namespace Modules\Connections\Policies;

use App\Models\User;
use Modules\Connections\Models\PlatformConnection;
use Modules\Workspaces\Models\Workspace;

class PlatformConnectionPolicy
{
    public function viewAny(User $user, Workspace $workspace): bool
    {
        return $user->canAccessWorkspace($workspace);
    }

    public function manage(User $user, Workspace $workspace): bool
    {
        if (! $user->canAccessWorkspace($workspace)) {
            return false;
        }

        return $user->isSuperAdmin()
            || $user->isAgencyAdmin()
            || $user->hasRole('operator');
    }

    public function update(User $user, PlatformConnection $connection): bool
    {
        return $user->canAccessWorkspace($connection->workspace);
    }

    public function delete(User $user, PlatformConnection $connection): bool
    {
        return $user->canAccessWorkspace($connection->workspace)
            && ($user->isSuperAdmin() || $user->isAgencyAdmin());
    }
}
