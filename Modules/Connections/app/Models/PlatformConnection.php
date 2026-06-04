<?php

namespace Modules\Connections\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Modules\Connections\Enums\ConnectionStatus;
use Modules\Connections\Enums\Platform;
use Modules\Workspaces\Models\Workspace;

class PlatformConnection extends Model
{
    protected $fillable = [
        'workspace_id',
        'platform',
        'access_token',
        'refresh_token',
        'token_expires_at',
        'status',
        'external_account_id',
        'external_account_name',
        'metadata',
    ];

    protected function casts(): array
    {
        return [
            'platform' => Platform::class,
            'status' => ConnectionStatus::class,
            'access_token' => 'encrypted',
            'refresh_token' => 'encrypted',
            'token_expires_at' => 'datetime',
            'metadata' => 'array',
        ];
    }

    public function workspace(): BelongsTo
    {
        return $this->belongsTo(Workspace::class);
    }

    public function assets(): HasMany
    {
        return $this->hasMany(ConnectedAsset::class, 'connection_id');
    }

    public function markExpired(): void
    {
        $this->update(['status' => ConnectionStatus::Expired]);
    }

    public function markError(): void
    {
        $this->update(['status' => ConnectionStatus::Error]);
    }
}
