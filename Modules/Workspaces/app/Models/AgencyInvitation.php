<?php

namespace Modules\Workspaces\Models;

use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Str;
use Modules\Workspaces\Enums\SystemRole;

class AgencyInvitation extends Model
{
    protected $fillable = [
        'agency_id',
        'invited_by',
        'email',
        'role',
        'token',
        'accepted_at',
        'expires_at',
    ];

    protected function casts(): array
    {
        return [
            'role' => SystemRole::class,
            'accepted_at' => 'datetime',
            'expires_at' => 'datetime',
        ];
    }

    public static function createForAgency(
        Agency $agency,
        User $invitedBy,
        string $email,
        SystemRole $role,
    ): self {
        return self::query()->updateOrCreate(
            [
                'agency_id' => $agency->id,
                'email' => strtolower($email),
            ],
            [
                'invited_by' => $invitedBy->id,
                'role' => $role,
                'token' => Str::random(64),
                'accepted_at' => null,
                'expires_at' => now()->addDays(7),
            ],
        );
    }

    public function agency(): BelongsTo
    {
        return $this->belongsTo(Agency::class);
    }

    public function inviter(): BelongsTo
    {
        return $this->belongsTo(User::class, 'invited_by');
    }

    public function isPending(): bool
    {
        return $this->accepted_at === null && $this->expires_at->isFuture();
    }

    public function acceptUrl(): string
    {
        return route('invitations.show', $this->token);
    }
}
