<?php

namespace Modules\Analytics\Policies;

use App\Models\User;
use Modules\Analytics\Models\CompetitorAccount;
use Modules\Workspaces\Models\Workspace;

class CompetitorAccountPolicy
{
    public function viewAny(User $user, Workspace $workspace): bool
    {
        return $user->can('view', $workspace);
    }

    public function manage(User $user, Workspace $workspace): bool
    {
        return $user->can('customizeDashboard', $workspace);
    }

    public function update(User $user, CompetitorAccount $competitor): bool
    {
        return $user->can('customizeDashboard', $competitor->workspace);
    }

    public function delete(User $user, CompetitorAccount $competitor): bool
    {
        return $user->can('customizeDashboard', $competitor->workspace);
    }
}
