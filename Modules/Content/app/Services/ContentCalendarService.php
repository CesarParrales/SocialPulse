<?php

namespace Modules\Content\Services;

use Carbon\Carbon;
use Illuminate\Support\Collection;
use Modules\Content\Enums\ContentDraftStatus;
use Modules\Content\Models\ContentCalendarEntry;
use Modules\Content\Models\ContentDraft;
use Modules\Workspaces\Models\Workspace;

class ContentCalendarService
{
    /**
     * @return Collection<int, ContentCalendarEntry>
     */
    public function entriesForMonth(Workspace $workspace, string $month): Collection
    {
        $start = Carbon::parse($month.'-01')->startOfMonth();
        $end = $start->copy()->endOfMonth();

        return ContentCalendarEntry::query()
            ->where('workspace_id', $workspace->id)
            ->whereBetween('scheduled_at', [$start, $end])
            ->with('draft')
            ->orderBy('scheduled_at')
            ->get();
    }

    /**
     * @return Collection<int, ContentDraft>
     */
    public function draftsForWorkspace(Workspace $workspace): Collection
    {
        return ContentDraft::query()
            ->where('workspace_id', $workspace->id)
            ->whereNotIn('status', [ContentDraftStatus::Cancelled])
            ->with('publishedLink')
            ->orderByDesc('updated_at')
            ->get();
    }
}
