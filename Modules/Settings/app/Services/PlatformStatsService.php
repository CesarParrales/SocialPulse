<?php

namespace Modules\Settings\Services;

use Illuminate\Support\Facades\DB;
use Modules\Connections\Enums\ConnectionStatus;
use Modules\Connections\Models\PlatformConnection;
use Modules\Workspaces\Models\Agency;
use Modules\Workspaces\Models\Workspace;

class PlatformStatsService
{
    /**
     * @return array<string, int>
     */
    public function summary(): array
    {
        return [
            'agencies' => Agency::query()->count(),
            'workspaces' => Workspace::query()->count(),
            'users' => (int) DB::table('users')->count(),
            'connections' => PlatformConnection::query()
                ->where('status', ConnectionStatus::Active)
                ->count(),
        ];
    }
}
