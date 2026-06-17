<?php

namespace Modules\Content\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;
use Modules\Content\Enums\ContentChannel;
use Modules\Content\Enums\ContentType;
use Modules\Content\Http\Requests\ReviewContentDraftRequest;
use Modules\Content\Http\Requests\StoreContentCalendarEntryRequest;
use Modules\Content\Http\Requests\StoreContentDraftRequest;
use Modules\Content\Http\Requests\UpdateContentDraftRequest;
use Modules\Content\Models\ContentCalendarEntry;
use Modules\Content\Models\ContentDraft;
use Modules\Content\Services\ContentCalendarService;
use Modules\Content\Services\ContentPublishService;
use Modules\Content\Services\ContentWorkflowService;
use Modules\Workspaces\Models\Workspace;

class WorkspaceContentController extends Controller
{
    public function index(
        Request $request,
        Workspace $workspace,
        ContentCalendarService $calendar,
    ): Response {
        $this->authorize('viewAny', [ContentDraft::class, $workspace]);

        $month = $request->string('month', now()->format('Y-m'))->value();

        return Inertia::render('Content/Calendar', [
            'workspace' => $workspace->only(['id', 'name']),
            'month' => $month,
            'canManage' => $request->user()?->can('create', [ContentDraft::class, $workspace]) ?? false,
            'canReview' => $request->user()?->isClientReadonly()
                || $request->user()?->isAgencyAdmin()
                || $request->user()?->isSuperAdmin(),
            'calendarEntries' => $calendar->entriesForMonth($workspace, $month)
                ->map->toFrontend()
                ->values(),
            'drafts' => $calendar->draftsForWorkspace($workspace)
                ->map->toFrontend()
                ->values(),
            'channelOptions' => collect(ContentChannel::cases())->map(fn (ContentChannel $channel) => [
                'value' => $channel->value,
                'label' => $channel->label(),
            ])->values(),
            'typeOptions' => collect(ContentType::cases())->map(fn (ContentType $type) => [
                'value' => $type->value,
                'label' => $type->label(),
            ])->values(),
        ]);
    }

    public function storeEntry(
        StoreContentCalendarEntryRequest $request,
        Workspace $workspace,
    ): RedirectResponse {
        ContentCalendarEntry::query()->create([
            'workspace_id' => $workspace->id,
            'created_by' => $request->user()?->id,
            ...$request->validated(),
            'scheduled_at' => $request->date('scheduled_at'),
        ]);

        return back()->with('success', __('app.content.entry_created'));
    }

    public function storeDraft(
        StoreContentDraftRequest $request,
        Workspace $workspace,
    ): RedirectResponse {
        $data = $request->validated();

        if (! empty($data['calendar_entry_id'])) {
            $entry = ContentCalendarEntry::query()->find($data['calendar_entry_id']);

            if ($entry === null || $entry->workspace_id !== $workspace->id) {
                abort(404);
            }
        }

        ContentDraft::query()->create([
            'workspace_id' => $workspace->id,
            'created_by' => $request->user()?->id,
            'title' => $data['title'],
            'caption' => $data['caption'] ?? null,
            'channel' => $data['channel'],
            'content_type' => $data['content_type'],
            'calendar_entry_id' => $data['calendar_entry_id'] ?? null,
            'media_url' => $data['media_url'] ?? null,
            'scheduled_at' => isset($data['scheduled_at']) ? $request->date('scheduled_at') : null,
        ]);

        return back()->with('success', __('app.content.draft_created'));
    }

    public function updateDraft(
        UpdateContentDraftRequest $request,
        Workspace $workspace,
        ContentDraft $draft,
    ): RedirectResponse {
        $this->ensureDraftBelongsToWorkspace($workspace, $draft);

        $draft->update([
            ...$request->validated(),
            'scheduled_at' => $request->filled('scheduled_at')
                ? $request->date('scheduled_at')
                : null,
        ]);

        return back()->with('success', __('app.content.draft_updated'));
    }

    public function submitDraft(
        Request $request,
        Workspace $workspace,
        ContentDraft $draft,
        ContentWorkflowService $workflow,
    ): RedirectResponse {
        $this->ensureDraftBelongsToWorkspace($workspace, $draft);
        $this->authorize('submit', $draft);

        $workflow->submitForReview($draft, $request->user());

        return back()->with('success', __('app.content.submitted_for_review'));
    }

    public function reviewDraft(
        ReviewContentDraftRequest $request,
        Workspace $workspace,
        ContentDraft $draft,
        ContentWorkflowService $workflow,
    ): RedirectResponse {
        $this->ensureDraftBelongsToWorkspace($workspace, $draft);

        if ($request->string('action')->value() === 'approve') {
            $workflow->approve($draft, $request->user(), $request->string('review_notes')->value() ?: null);
            $message = __('app.content.approved');
        } else {
            $workflow->reject(
                $draft,
                $request->user(),
                $request->string('review_notes')->value(),
            );
            $message = __('app.content.rejected');
        }

        return back()->with('success', $message);
    }

    public function publishDraft(
        Request $request,
        Workspace $workspace,
        ContentDraft $draft,
        ContentPublishService $publisher,
    ): RedirectResponse {
        $this->ensureDraftBelongsToWorkspace($workspace, $draft);
        $this->authorize('publish', $draft);

        try {
            $publisher->publish($workspace, $draft);

            return back()->with('success', __('app.content.published'));
        } catch (\Throwable $exception) {
            return back()->withErrors([
                'publish' => $exception->getMessage(),
            ]);
        }
    }

    private function ensureDraftBelongsToWorkspace(Workspace $workspace, ContentDraft $draft): void
    {
        if ($draft->workspace_id !== $workspace->id) {
            abort(404);
        }
    }
}
