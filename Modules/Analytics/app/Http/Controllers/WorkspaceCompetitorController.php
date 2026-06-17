<?php

namespace Modules\Analytics\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;
use Modules\Analytics\Http\Requests\SaveCompetitorInsightRequest;
use Modules\Analytics\Http\Requests\StoreCompetitorRequest;
use Modules\Analytics\Http\Requests\UpdateCompetitorRequest;
use Modules\Analytics\Models\CompetitorAccount;
use Modules\Analytics\Models\CompetitorInsight;
use Modules\Analytics\Services\CompetitorBenchmarkService;
use Modules\Analytics\Services\CompetitorInsightPromptBuilder;
use Modules\Connections\Services\WorkspaceAssetScopeService;
use Modules\Workspaces\Models\Workspace;

class WorkspaceCompetitorController extends Controller
{
    public function index(
        Request $request,
        Workspace $workspace,
        CompetitorBenchmarkService $benchmarks,
        WorkspaceAssetScopeService $assetScope,
    ): Response {
        $this->authorize('view', $workspace);

        $scope = $assetScope->resolve(
            $workspace,
            $request->filled('asset_id') ? $request->integer('asset_id') : null,
        );

        $competitors = CompetitorAccount::query()
            ->where('workspace_id', $workspace->id)
            ->orderBy('sort_order')
            ->orderBy('name')
            ->get()
            ->map->toBenchmarkRow();

        $insight = CompetitorInsight::query()
            ->where('workspace_id', $workspace->id)
            ->first();

        return Inertia::render('Analytics/Competitors', [
            'workspace' => $workspace->only(['id', 'name', 'industry_category', 'region']),
            'canManage' => $request->user()?->can('customizeDashboard', $workspace) ?? false,
            'assetScope' => [
                'assets' => $assetScope->serializeForFrontend($scope['all']),
                'selected_asset_id' => $scope['selected_asset_id'],
                'route' => 'workspaces.competitors.index',
            ],
            'competitors' => $competitors,
            'benchmarkOverview' => $benchmarks->buildOverview($workspace, $scope['selected']),
            'insight' => $insight?->toFrontend(),
        ]);
    }

    public function store(
        StoreCompetitorRequest $request,
        Workspace $workspace,
    ): RedirectResponse {
        CompetitorAccount::query()->create([
            'workspace_id' => $workspace->id,
            ...$request->validated(),
            'sort_order' => (int) CompetitorAccount::query()->where('workspace_id', $workspace->id)->count(),
        ]);

        return back()->with('success', __('app.competitors.created'));
    }

    public function update(
        UpdateCompetitorRequest $request,
        Workspace $workspace,
        CompetitorAccount $competitor,
    ): RedirectResponse {
        abort_unless($competitor->workspace_id === $workspace->id, 404);

        $competitor->update($request->validated());

        return back()->with('success', __('app.competitors.updated'));
    }

    public function destroy(
        Request $request,
        Workspace $workspace,
        CompetitorAccount $competitor,
    ): RedirectResponse {
        $this->authorize('delete', $competitor);
        abort_unless($competitor->workspace_id === $workspace->id, 404);

        $competitor->delete();

        return back()->with('success', __('app.competitors.deleted'));
    }

    public function generatePrompt(
        Request $request,
        Workspace $workspace,
        CompetitorInsightPromptBuilder $promptBuilder,
        WorkspaceAssetScopeService $assetScope,
    ): RedirectResponse {
        $this->authorize('customizeDashboard', $workspace);

        $scope = $assetScope->resolve(
            $workspace,
            $request->filled('asset_id') ? $request->integer('asset_id') : null,
        );

        $prompt = $promptBuilder->build($workspace, $scope['selected']);

        CompetitorInsight::query()->updateOrCreate(
            ['workspace_id' => $workspace->id],
            [
                'prompt_text' => $prompt,
                'prompt_generated_at' => now(),
                'updated_by' => $request->user()?->id,
            ],
        );

        return back()->with('success', __('app.competitors.prompt_generated'));
    }

    public function saveInsight(
        SaveCompetitorInsightRequest $request,
        Workspace $workspace,
    ): RedirectResponse {
        $validated = $request->validated();

        CompetitorInsight::query()->updateOrCreate(
            ['workspace_id' => $workspace->id],
            [
                'ai_draft_text' => $validated['ai_draft_text'] ?? null,
                'reviewed_text' => $validated['reviewed_text'] ?? null,
                'reviewed_at' => filled($validated['reviewed_text'] ?? null) ? now() : null,
                'updated_by' => $request->user()?->id,
            ],
        );

        return back()->with('success', __('app.competitors.insight_saved'));
    }
}
