<?php

namespace Modules\Dashboard\Services;

use Illuminate\Support\Str;
use Modules\Workspaces\Models\Workspace;

class PublicDashboardShareService
{
    public function isEnabled(Workspace $workspace): bool
    {
        return $workspace->public_dashboard_token !== null
            && $workspace->public_dashboard_enabled_at !== null;
    }

    public function url(Workspace $workspace): ?string
    {
        if (! $this->isEnabled($workspace)) {
            return null;
        }

        return route('public.dashboard', [
            'token' => $workspace->public_dashboard_token,
        ]);
    }

    public function enable(Workspace $workspace): Workspace
    {
        $workspace->update([
            'public_dashboard_token' => $workspace->public_dashboard_token ?? $this->generateToken(),
            'public_dashboard_enabled_at' => now(),
        ]);

        return $workspace->refresh();
    }

    public function disable(Workspace $workspace): Workspace
    {
        $workspace->update([
            'public_dashboard_enabled_at' => null,
        ]);

        return $workspace->refresh();
    }

    public function regenerate(Workspace $workspace): Workspace
    {
        $workspace->update([
            'public_dashboard_token' => $this->generateToken(),
            'public_dashboard_enabled_at' => now(),
        ]);

        return $workspace->refresh();
    }

    public function resolveWorkspace(string $token): ?Workspace
    {
        return Workspace::query()
            ->where('public_dashboard_token', $token)
            ->whereNotNull('public_dashboard_enabled_at')
            ->first();
    }

    private function generateToken(): string
    {
        return Str::lower(Str::random(48));
    }
}
