<?php

namespace App\Models;

use Database\Factories\UserFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Hidden;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Modules\Workspaces\Enums\SystemRole;
use Modules\Workspaces\Enums\WorkspaceMemberRole;
use Modules\Workspaces\Models\Agency;
use Modules\Workspaces\Models\Workspace;
use Spatie\Permission\Traits\HasRoles;

#[Fillable(['name', 'email', 'password', 'agency_id', 'locale'])]
#[Hidden(['password', 'remember_token'])]
class User extends Authenticatable
{
    /** @use HasFactory<UserFactory> */
    use HasFactory, HasRoles, Notifiable;

    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
        ];
    }

    public function agency(): BelongsTo
    {
        return $this->belongsTo(Agency::class);
    }

    public function workspaces(): BelongsToMany
    {
        return $this->belongsToMany(Workspace::class, 'workspace_user')
            ->withPivot('role')
            ->withTimestamps();
    }

    public function isSuperAdmin(): bool
    {
        return $this->hasRole(SystemRole::SuperAdmin->value);
    }

    public function isAgencyAdmin(): bool
    {
        return $this->hasRole(SystemRole::AgencyAdmin->value);
    }

    public function isClientReadonly(): bool
    {
        return $this->hasRole(SystemRole::ClientReadonly->value);
    }

    public function isWorkspaceClient(Workspace $workspace): bool
    {
        if ($this->isClientReadonly()) {
            return true;
        }

        return $this->workspaceMemberRole($workspace) === WorkspaceMemberRole::ClientReadonly;
    }

    public function clientHomeUrl(): string
    {
        $workspace = Workspace::query()
            ->accessibleBy($this)
            ->orderBy('name')
            ->first();

        if ($workspace !== null) {
            return route('workspaces.dashboard', $workspace);
        }

        return route('workspaces.index');
    }

    public function canAccessWorkspace(Workspace $workspace): bool
    {
        if ($this->isSuperAdmin()) {
            return true;
        }

        if ($this->agency_id !== $workspace->agency_id) {
            return false;
        }

        if ($this->isAgencyAdmin()) {
            return true;
        }

        return $this->workspaces()
            ->whereKey($workspace->getKey())
            ->exists();
    }

    public function workspaceMemberRole(Workspace $workspace): ?WorkspaceMemberRole
    {
        $pivotRole = $this->workspaces()
            ->whereKey($workspace->getKey())
            ->first()
            ?->pivot
            ?->role;

        return $pivotRole !== null
            ? WorkspaceMemberRole::from($pivotRole)
            : null;
    }
}
