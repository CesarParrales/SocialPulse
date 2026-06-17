<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class RestrictClientReadonly
{
    /** @var list<string> */
    private const ALLOWED_ROUTE_NAMES = [
        'workspaces.index',
        'workspaces.dashboard',
        'workspaces.content.index',
        'workspaces.content.drafts.review',
        'workspaces.content.drafts.submit',
        'profile.edit',
        'profile.update',
        'profile.destroy',
        'locale.update',
        'notifications.index',
        'notifications.read',
        'notifications.read-all',
        'verification.notice',
        'verification.verify',
        'verification.send',
        'logout',
    ];

    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();

        if ($user === null || ! $user->isClientReadonly()) {
            return $next($request);
        }

        $routeName = $request->route()?->getName();

        if ($routeName === 'dashboard') {
            return redirect()->to($user->clientHomeUrl());
        }

        if ($routeName === null || $this->isAllowed($routeName)) {
            return $next($request);
        }

        return redirect()->to($user->clientHomeUrl());
    }

    private function isAllowed(string $routeName): bool
    {
        foreach (self::ALLOWED_ROUTE_NAMES as $allowed) {
            if ($routeName === $allowed) {
                return true;
            }
        }

        return false;
    }
}
