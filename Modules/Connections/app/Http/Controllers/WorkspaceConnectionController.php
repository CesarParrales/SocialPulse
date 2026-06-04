<?php

namespace Modules\Connections\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Modules\Connections\Enums\ConnectionStatus;
use Modules\Connections\Enums\Platform;
use Modules\Connections\Models\PlatformConnection;
use Modules\Connections\Services\ConnectedAssetService;
use Modules\Connections\Services\Google\GoogleOAuthService;
use Modules\Connections\Services\Meta\MetaGraphService;
use Modules\Connections\Services\Meta\MetaOAuthService;
use Modules\Workspaces\Models\Workspace;
use Throwable;

class WorkspaceConnectionController extends Controller
{
    public function index(
        Request $request,
        Workspace $workspace,
        MetaGraphService $metaGraph,
    ): Response {
        $this->authorize('viewAny', [PlatformConnection::class, $workspace]);

        $connections = PlatformConnection::query()
            ->where('workspace_id', $workspace->id)
            ->with('assets')
            ->get();

        $discoveredAssets = [];

        $metaConnection = $connections->firstWhere('platform', Platform::Meta);

        if ($metaConnection !== null && config('connections.meta.app_id')) {
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

        return Inertia::render('Connections/Index', [
            'workspace' => $workspace->only(['id', 'name']),
            'connections' => $connections,
            'discoveredAssets' => $discoveredAssets,
            'canManage' => $request->user()->can('manage', [PlatformConnection::class, $workspace]),
            'metaConfigured' => filled(config('connections.meta.app_id')),
            'googleConfigured' => filled(config('connections.google.client_id')),
        ]);
    }

    public function metaRedirect(
        Request $request,
        Workspace $workspace,
        MetaOAuthService $metaOAuth,
    ): RedirectResponse {
        $this->authorize('manage', [PlatformConnection::class, $workspace]);

        abort_unless(config('connections.meta.app_id'), 503, 'Meta OAuth no está configurado.');

        return redirect()->away(
            $metaOAuth->authorizationUrl($workspace, $request->user()->id),
        );
    }

    public function metaCallback(Request $request, MetaOAuthService $metaOAuth): RedirectResponse
    {
        $request->validate([
            'code' => ['required', 'string'],
            'state' => ['required', 'string'],
        ]);

        $state = $metaOAuth->decodeState($request->string('state')->value());

        abort_unless($state['user_id'] === $request->user()->id, 403);

        $workspace = Workspace::query()->findOrFail($state['workspace_id']);

        $this->authorize('manage', [PlatformConnection::class, $workspace]);

        $metaOAuth->connect($workspace, $request->string('code')->value());

        return redirect()
            ->route('workspaces.connections.index', $workspace)
            ->with('success', 'Cuenta Meta conectada. Selecciona los activos a monitorear.');
    }

    public function googleRedirect(
        Request $request,
        Workspace $workspace,
        GoogleOAuthService $googleOAuth,
    ): RedirectResponse {
        $this->authorize('manage', [PlatformConnection::class, $workspace]);

        abort_unless(config('connections.google.client_id'), 503, 'Google OAuth no está configurado.');

        return redirect()->away(
            $googleOAuth->authorizationUrl($workspace, $request->user()->id),
        );
    }

    public function googleCallback(Request $request, GoogleOAuthService $googleOAuth): RedirectResponse
    {
        $request->validate([
            'code' => ['required', 'string'],
            'state' => ['required', 'string'],
        ]);

        $state = $googleOAuth->decodeState($request->string('state')->value());

        abort_unless($state['user_id'] === $request->user()->id, 403);

        $workspace = Workspace::query()->findOrFail($state['workspace_id']);

        $this->authorize('manage', [PlatformConnection::class, $workspace]);

        $googleOAuth->connect($workspace, $request->string('code')->value());

        return redirect()
            ->route('workspaces.connections.index', $workspace)
            ->with('success', 'Cuenta Google Ads conectada.');
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

        return back()->with('success', 'Activos actualizados.');
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

        return back()->with('success', 'Conexión desactivada. El histórico se conserva.');
    }
}
