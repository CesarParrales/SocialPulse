<?php

namespace Modules\Workspaces\Models;

use App\Models\User;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Workspace extends Model
{
    protected $fillable = [
        'agency_id',
        'name',
        'industry_category',
        'region',
        'timezone',
        'settings',
        'public_dashboard_token',
        'public_dashboard_enabled_at',
    ];

    protected function casts(): array
    {
        return [
            'settings' => 'array',
            'public_dashboard_enabled_at' => 'datetime',
        ];
    }

    public function isPublicDashboardEnabled(): bool
    {
        return $this->public_dashboard_token !== null
            && $this->public_dashboard_enabled_at !== null;
    }

    public function agency(): BelongsTo
    {
        return $this->belongsTo(Agency::class);
    }

    public function members(): BelongsToMany
    {
        return $this->belongsToMany(User::class, 'workspace_user')
            ->withPivot('role')
            ->withTimestamps();
    }

    public function scopeAccessibleBy(Builder $query, User $user): Builder
    {
        if ($user->isSuperAdmin()) {
            return $query;
        }

        if ($user->isAgencyAdmin() && $user->agency_id !== null) {
            return $query->where('agency_id', $user->agency_id);
        }

        return $query->whereIn('id', $user->workspaces()->select('workspaces.id'));
    }
}
