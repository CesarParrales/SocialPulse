<?php

namespace Modules\Connections\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;
use Modules\Connections\Enums\ConnectionStatus;
use Modules\Connections\Enums\Platform;
use Modules\Connections\Models\PlatformConnection;
use Modules\Connections\Services\ConnectedAssetService;
use Modules\Connections\Services\Google\GoogleOAuthService;
use Modules\Connections\Services\LinkedIn\LinkedInApiService;
use Modules\Connections\Services\LinkedIn\LinkedInOAuthService;
use Modules\Connections\Services\Meta\MetaGraphService;
use Modules\Connections\Services\Meta\MetaOAuthService;
use Modules\Connections\Services\Meta\MetaSystemUserService;
use Modules\Connections\Services\TikTok\TikTokApiService;
use Modules\Connections\Services\TikTok\TikTokOAuthService;
use Modules\Connections\Services\YouTube\YouTubeApiService;
use Modules\Connections\Services\YouTube\YouTubeOAuthService;
use Modules\Connections\Support\PlatformCatalog;
use Modules\Settings\Services\IntegrationConfigResolver;
use Modules\Settings\Services\PlatformIntegrationsService;
use Modules\Workspaces\Models\Workspace;
use Throwable;

class WorkspaceConnectionController extends Controller
{
    public function index(
        Request $request,
        Workspace $workspace,
        MetaGraphService $metaGraph,
        TikTokApiService $tiktokApi,
        LinkedInApiService $linkedInApi,
        YouTubeApiService $youTubeApi,
        IntegrationConfigResolver $configResolver,
        PlatformIntegrationsService $integrations,
    ): Response {
        $this->authorize('viewAny', [PlatformConnection::class, $workspace]);

        $connections = PlatformConnection::query()
            ->where('workspace_id', $workspace->id)
            ->with('assets')
            ->get();

        $discoveredAssets = [];

        $metaConnection = $connections->firstWhere('platform', Platform::Meta);

        if ($metaConnection !== null) {
            try {
                $activeIds = $metaConnection->assets->pluck('platform_asset_id')->all();

                $discoveredAssets['meta'] = $metaGraph
                    ->discoverAssets($metaConnection)
                    ->map(fn (array $asset) => [
                        ...$asset,
                        'selected' => in_array($asset['id'], $activeIds, true),
                    ])
                    ->values()
                    ->all();
            } catch (Throwable) {
                $metaConnection->markError();
                $discoveredAssets['meta'] = [];
            }
        }

        $tiktokConnection = $connections->firstWhere('platform', Platform::TikTok);

        if ($tiktokConnection !== null) {
            try {
                $activeIds = $tiktokConnection->assets->pluck('platform_asset_id')->all();

                $discoveredAssets['tiktok'] = $tiktokApi
                    ->discoverAssets($tiktokConnection)
                    ->map(fn (array $asset) => [
                        ...$asset,
                        'selected' => in_array($asset['id'], $activeIds, true),
                    ])
                    ->values()
                    ->all();
            } catch (Throwable) {
                $tiktokConnection->markError();
                $discoveredAssets['tiktok'] = [];
            }
        }

        $linkedInConnection = $connections->firstWhere('platform', Platform::LinkedIn);

        if ($linkedInConnection !== null) {
            try {
                $activeIds = $linkedInConnection->assets->pluck('platform_asset_id')->all();

                $discoveredAssets['linkedin'] = $linkedInApi
                    ->discoverAssets($linkedInConnection)
                    ->map(fn (array $asset) => [
                        ...$asset,
                        'selected' => in_array($asset['id'], $activeIds, true),
                    ])
                    ->values()
                    ->all();
            } catch (Throwable) {
                $linkedInConnection->markError();
                $discoveredAssets['linkedin'] = [];
            }
        }

        $youTubeConnection = $connections->firstWhere('platform', Platform::YouTube);

        if ($youTubeConnection !== null) {
            try {
                $activeIds = $youTubeConnection->assets->pluck('platform_asset_id')->all();

                $discoveredAssets['youtube'] = $youTubeApi
                    ->discoverAssets($youTubeConnection)
                    ->map(fn (array $asset) => [
                        ...$asset,
                        'selected' => in_array($asset['id'], $activeIds, true),
                    ])
                    ->values()
                    ->all();
            } catch (Throwable) {
                $youTubeConnection->markError();
                $discoveredAssets['youtube'] = [];
            }
        }

        return Inertia::render('Connections/Index', [
            'workspace' => $workspace->only(['id', 'name']),
            'connections' => $connections->map(fn (PlatformConnection $connection) => [
                ...$connection->only(['id', 'platform', 'status', 'token_expires_at']),
                'auth_mode' => $connection->metaAuthMode()->value,
                'assets' => $connection->assets,
            ]),
            'discoveredAssets' => $discoveredAssets,
            'canManage' => $request->user()->can('manage', [PlatformConnection::class, $workspace]),
            'metaConfigured' => $configResolver->isMetaOAuthConfigured($workspace->agency_id),
            'metaSystemUserConfigured' => $configResolver->isMetaSystemUserConfigured($workspace->agency_id),
            'googleConfigured' => $configResolver->isGoogleOAuthConfigured($workspace->agency_id),
            'tiktokConfigured' => $configResolver->isTikTokOAuthConfigured($workspace->agency_id),
            'linkedInConfigured' => $configResolver->isLinkedInOAuthConfigured($workspace->agency_id),
            'youTubeConfigured' => $configResolver->isYouTubeOAuthConfigured($workspace->agency_id),
            'canViewSettings' => $request->user()->isSuperAdmin()
                || $request->user()->isAgencyAdmin(),
            'integrations' => $integrations->status($workspace->agency_id),
            'platformCatalog' => collect(PlatformCatalog::all())->map(fn (array $platform) => [
                'key' => $platform['key'],
                'label' => $platform['label'],
                'status' => $platform['status'],
                'phase' => $platform['phase'],
                'channels' => collect($platform['channels'])->map(fn (array $channel) => [
                    'key' => $channel['key'],
                    'label' => $channel['label'],
                    'capabilities' => $channel['capabilities'] ?? [],
                ])->values()->all(),
            ])->values()->all(),
        ]);
    }

    public function metaRedirect(
        Request $request,
        Workspace $workspace,
        MetaOAuthService $metaOAuth,
    ): RedirectResponse {
        $this->authorize('manage', [PlatformConnection::class, $workspace]);

        abort_unless(
            app(IntegrationConfigResolver::class)->isMetaOAuthConfigured($workspace->agency_id),
            503,
            'Meta OAuth no está configurado.',
        );

        return redirect()->away(
            $metaOAuth->authorizationUrl($workspace, $request->user()->id),
        );
    }

    public function metaCallback(Request $request, MetaOAuthService $metaOAuth): RedirectResponse
    {
        if ($request->filled('error')) {
            return $this->oauthDeniedRedirect(
                $request,
                $metaOAuth,
                $request->string('error_description')->value()
                    ?: $request->string('error')->value(),
            );
        }

        $request->validate([
            'code' => ['required', 'string'],
            'state' => ['required', 'string'],
        ]);

        $state = $metaOAuth->decodeState($request->string('state')->value());

        abort_unless($state['user_id'] === $request->user()->id, 403);

        $workspace = Workspace::query()->findOrFail($state['workspace_id']);

        $this->authorize('manage', [PlatformConnection::class, $workspace]);

        try {
            $metaOAuth->connect($workspace, $request->string('code')->value());
        } catch (Throwable $exception) {
            return redirect()
                ->route('workspaces.connections.index', $workspace)
                ->withErrors([
                    'oauth' => __('app.flash.connections.oauth_failed', [
                        'message' => $exception->getMessage(),
                    ]),
                ]);
        }

        return redirect()
            ->route('workspaces.connections.index', $workspace)
            ->with('success', __('app.flash.connections.meta_connected'));
    }

    public function metaSystemUserConnect(
        Request $request,
        Workspace $workspace,
        MetaSystemUserService $systemUser,
    ): RedirectResponse {
        $this->authorize('manage', [PlatformConnection::class, $workspace]);

        abort_unless($systemUser->isConfigured($workspace->agency_id), 503, 'Meta System User no está configurado.');

        try {
            $systemUser->connect($workspace);
        } catch (Throwable $exception) {
            return back()->withErrors([
                'oauth' => __('app.flash.connections.oauth_failed', [
                    'message' => $exception->getMessage(),
                ]),
            ]);
        }

        return redirect()
            ->route('workspaces.connections.index', $workspace)
            ->with('success', __('app.flash.connections.meta_system_user_connected'));
    }

    public function googleRedirect(
        Request $request,
        Workspace $workspace,
        GoogleOAuthService $googleOAuth,
    ): RedirectResponse {
        $this->authorize('manage', [PlatformConnection::class, $workspace]);

        abort_unless(
            app(IntegrationConfigResolver::class)->isGoogleOAuthConfigured($workspace->agency_id),
            503,
            'Google OAuth no está configurado.',
        );

        return redirect()->away(
            $googleOAuth->authorizationUrl($workspace, $request->user()->id),
        );
    }

    public function googleCallback(Request $request, GoogleOAuthService $googleOAuth): RedirectResponse
    {
        if ($request->filled('error')) {
            return $this->oauthDeniedRedirect(
                $request,
                $googleOAuth,
                $request->string('error_description')->value()
                    ?: $request->string('error')->value(),
            );
        }

        $request->validate([
            'code' => ['required', 'string'],
            'state' => ['required', 'string'],
        ]);

        $state = $googleOAuth->decodeState($request->string('state')->value());

        abort_unless($state['user_id'] === $request->user()->id, 403);

        $workspace = Workspace::query()->findOrFail($state['workspace_id']);

        $this->authorize('manage', [PlatformConnection::class, $workspace]);

        try {
            $googleOAuth->connect($workspace, $request->string('code')->value());
        } catch (Throwable $exception) {
            return redirect()
                ->route('workspaces.connections.index', $workspace)
                ->withErrors([
                    'oauth' => __('app.flash.connections.oauth_failed', [
                        'message' => $exception->getMessage(),
                    ]),
                ]);
        }

        return redirect()
            ->route('workspaces.connections.index', $workspace)
            ->with('success', __('app.flash.connections.google_connected'));
    }

    public function tiktokRedirect(
        Request $request,
        Workspace $workspace,
        TikTokOAuthService $tiktokOAuth,
    ): RedirectResponse {
        $this->authorize('manage', [PlatformConnection::class, $workspace]);

        abort_unless(
            app(IntegrationConfigResolver::class)->isTikTokOAuthConfigured($workspace->agency_id),
            503,
            'TikTok OAuth no está configurado.',
        );

        return redirect()->away(
            $tiktokOAuth->authorizationUrl($workspace, $request->user()->id),
        );
    }

    public function tiktokCallback(Request $request, TikTokOAuthService $tiktokOAuth): RedirectResponse
    {
        if ($request->filled('error')) {
            return $this->oauthDeniedRedirect(
                $request,
                $tiktokOAuth,
                $request->string('error_description')->value()
                    ?: $request->string('error')->value(),
            );
        }

        $request->validate([
            'code' => ['required', 'string'],
            'state' => ['required', 'string'],
        ]);

        $state = $tiktokOAuth->decodeState($request->string('state')->value());

        abort_unless($state['user_id'] === $request->user()->id, 403);

        $workspace = Workspace::query()->findOrFail($state['workspace_id']);

        $this->authorize('manage', [PlatformConnection::class, $workspace]);

        try {
            $tiktokOAuth->connect($workspace, $request->string('code')->value());
        } catch (Throwable $exception) {
            return redirect()
                ->route('workspaces.connections.index', $workspace)
                ->withErrors([
                    'oauth' => __('app.flash.connections.oauth_failed', [
                        'message' => $exception->getMessage(),
                    ]),
                ]);
        }

        return redirect()
            ->route('workspaces.connections.index', $workspace)
            ->with('success', __('app.flash.connections.tiktok_connected'));
    }

    public function linkedInRedirect(
        Request $request,
        Workspace $workspace,
        LinkedInOAuthService $linkedInOAuth,
    ): RedirectResponse {
        $this->authorize('manage', [PlatformConnection::class, $workspace]);

        abort_unless(
            app(IntegrationConfigResolver::class)->isLinkedInOAuthConfigured($workspace->agency_id),
            503,
            'LinkedIn OAuth no está configurado.',
        );

        return redirect()->away(
            $linkedInOAuth->authorizationUrl($workspace, $request->user()->id),
        );
    }

    public function linkedInCallback(Request $request, LinkedInOAuthService $linkedInOAuth): RedirectResponse
    {
        if ($request->filled('error')) {
            return $this->oauthDeniedRedirect(
                $request,
                $linkedInOAuth,
                $request->string('error_description')->value()
                    ?: $request->string('error')->value(),
            );
        }

        $request->validate([
            'code' => ['required', 'string'],
            'state' => ['required', 'string'],
        ]);

        $state = $linkedInOAuth->decodeState($request->string('state')->value());

        abort_unless($state['user_id'] === $request->user()->id, 403);

        $workspace = Workspace::query()->findOrFail($state['workspace_id']);

        $this->authorize('manage', [PlatformConnection::class, $workspace]);

        try {
            $linkedInOAuth->connect($workspace, $request->string('code')->value());
        } catch (Throwable $exception) {
            return redirect()
                ->route('workspaces.connections.index', $workspace)
                ->withErrors([
                    'oauth' => __('app.flash.connections.oauth_failed', [
                        'message' => $exception->getMessage(),
                    ]),
                ]);
        }

        return redirect()
            ->route('workspaces.connections.index', $workspace)
            ->with('success', __('app.flash.connections.linkedin_connected'));
    }

    public function youTubeRedirect(
        Request $request,
        Workspace $workspace,
        YouTubeOAuthService $youTubeOAuth,
    ): RedirectResponse {
        $this->authorize('manage', [PlatformConnection::class, $workspace]);

        abort_unless(
            app(IntegrationConfigResolver::class)->isYouTubeOAuthConfigured($workspace->agency_id),
            503,
            'YouTube OAuth no está configurado.',
        );

        return redirect()->away(
            $youTubeOAuth->authorizationUrl($workspace, $request->user()->id),
        );
    }

    public function youTubeCallback(Request $request, YouTubeOAuthService $youTubeOAuth): RedirectResponse
    {
        if ($request->filled('error')) {
            return $this->oauthDeniedRedirect(
                $request,
                $youTubeOAuth,
                $request->string('error_description')->value()
                    ?: $request->string('error')->value(),
            );
        }

        $request->validate([
            'code' => ['required', 'string'],
            'state' => ['required', 'string'],
        ]);

        $state = $youTubeOAuth->decodeState($request->string('state')->value());

        abort_unless($state['user_id'] === $request->user()->id, 403);

        $workspace = Workspace::query()->findOrFail($state['workspace_id']);

        $this->authorize('manage', [PlatformConnection::class, $workspace]);

        try {
            $youTubeOAuth->connect($workspace, $request->string('code')->value());
        } catch (Throwable $exception) {
            return redirect()
                ->route('workspaces.connections.index', $workspace)
                ->withErrors([
                    'oauth' => __('app.flash.connections.oauth_failed', [
                        'message' => $exception->getMessage(),
                    ]),
                ]);
        }

        return redirect()
            ->route('workspaces.connections.index', $workspace)
            ->with('success', __('app.flash.connections.youtube_connected'));
    }

    public function syncAssets(
        Request $request,
        Workspace $workspace,
        PlatformConnection $connection,
        ConnectedAssetService $assetService,
    ): RedirectResponse {
        $this->authorize('update', $connection);

        abort_unless($connection->workspace_id === $workspace->id, 404);

        $validated = $request->validate([
            'assets' => ['required', 'array'],
            'assets.*.type' => ['required', 'string'],
            'assets.*.id' => ['required', 'string'],
            'assets.*.name' => ['required', 'string'],
            'assets.*.selected' => ['required', 'boolean'],
            'assets.*.metadata' => ['nullable', 'array'],
        ]);

        $assetService->syncSelection($connection, $validated['assets']);

        return back()->with('success', __('app.flash.connections.assets_updated'));
    }

    public function destroy(
        Request $request,
        Workspace $workspace,
        PlatformConnection $connection,
    ): RedirectResponse {
        $this->authorize('delete', $connection);

        abort_unless($connection->workspace_id === $workspace->id, 404);

        $connection->update(['status' => ConnectionStatus::Expired]);
        $connection->assets()->update(['is_active' => false]);

        return back()->with('success', __('app.flash.connections.disconnected'));
    }

    private function oauthDeniedRedirect(
        Request $request,
        MetaOAuthService|GoogleOAuthService|TikTokOAuthService|LinkedInOAuthService|YouTubeOAuthService $oauthService,
        string $message,
    ): RedirectResponse {
        if (! $request->filled('state')) {
            return redirect()
                ->route('dashboard')
                ->withErrors([
                    'oauth' => __('app.flash.connections.oauth_denied', [
                        'message' => $message,
                    ]),
                ]);
        }

        try {
            $state = $oauthService->decodeState($request->string('state')->value());
        } catch (Throwable) {
            return redirect()
                ->route('dashboard')
                ->withErrors([
                    'oauth' => __('app.flash.connections.oauth_denied', [
                        'message' => $message,
                    ]),
                ]);
        }

        abort_unless($state['user_id'] === $request->user()->id, 403);

        $workspace = Workspace::query()->findOrFail($state['workspace_id']);

        return redirect()
            ->route('workspaces.connections.index', $workspace)
            ->withErrors([
                'oauth' => __('app.flash.connections.oauth_denied', [
                    'message' => $message,
                ]),
            ]);
    }
}
