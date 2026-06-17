<?php

namespace App\Http\Middleware;

use Illuminate\Http\Request;
use Inertia\Middleware;

class HandleInertiaRequests extends Middleware
{
    /**
     * The root template that is loaded on the first page visit.
     *
     * @var string
     */
    protected $rootView = 'app';

    /**
     * Determine the current asset version.
     */
    public function version(Request $request): ?string
    {
        return parent::version($request);
    }

    /**
     * Define the props that are shared by default.
     *
     * @return array<string, mixed>
     */
    public function share(Request $request): array
    {
        $user = $request->user();

        return [
            ...parent::share($request),
            'auth' => [
                'user' => $user ? [
                    'id' => $user->id,
                    'name' => $user->name,
                    'email' => $user->email,
                    'email_verified_at' => $user->email_verified_at,
                    'agency_id' => $user->agency_id,
                    'locale' => $user->locale ?? 'es',
                    'roles' => $user->getRoleNames()->values()->all(),
                    'is_client_readonly' => $user->isClientReadonly(),
                ] : null,
            ],
            'locale' => fn () => app()->getLocale(),
            'localeOptions' => [
                ['value' => 'es', 'label' => 'Español'],
                ['value' => 'en', 'label' => 'English'],
            ],
            'translations' => fn () => trans('app'),
            'flash' => [
                'success' => fn () => $request->session()->get('success'),
            ],
            'unreadNotificationsCount' => fn () => $user
                ? $user->unreadNotifications()->count()
                : 0,
            'clientHomeUrl' => fn () => $user?->isClientReadonly()
                ? $user->clientHomeUrl()
                : null,
        ];
    }
}
