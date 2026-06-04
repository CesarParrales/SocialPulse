<?php

namespace Modules\Workspaces\Models;

use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Modules\Workspaces\Enums\AgencyPlan;

class Agency extends Model
{
    protected $fillable = [
        'name',
        'plan',
        'billing_email',
        'settings',
    ];

    protected function casts(): array
    {
        return [
            'plan' => AgencyPlan::class,
            'settings' => 'array',
        ];
    }

    public function workspaces(): HasMany
    {
        return $this->hasMany(Workspace::class);
    }

    public function users(): HasMany
    {
        return $this->hasMany(User::class);
    }
}
