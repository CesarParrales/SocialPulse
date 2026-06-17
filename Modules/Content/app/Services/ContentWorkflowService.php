<?php

namespace Modules\Content\Services;

use App\Models\User;
use Illuminate\Validation\ValidationException;
use Modules\Content\Enums\ContentDraftStatus;
use Modules\Content\Models\ContentDraft;

class ContentWorkflowService
{
    public function submitForReview(ContentDraft $draft, User $user): ContentDraft
    {
        if (! $draft->status->isEditable()) {
            throw ValidationException::withMessages([
                'status' => __('app.content.errors.not_editable'),
            ]);
        }

        if (trim((string) $draft->caption) === '') {
            throw ValidationException::withMessages([
                'caption' => __('app.content.errors.caption_required'),
            ]);
        }

        $draft->update([
            'status' => ContentDraftStatus::PendingReview,
            'submitted_at' => now(),
            'reviewed_at' => null,
            'reviewed_by' => null,
            'review_notes' => null,
        ]);

        return $draft->refresh();
    }

    public function approve(ContentDraft $draft, User $user, ?string $notes = null): ContentDraft
    {
        if ($draft->status !== ContentDraftStatus::PendingReview) {
            throw ValidationException::withMessages([
                'status' => __('app.content.errors.not_pending_review'),
            ]);
        }

        $draft->update([
            'status' => ContentDraftStatus::Approved,
            'reviewed_by' => $user->id,
            'reviewed_at' => now(),
            'review_notes' => $notes,
        ]);

        return $draft->refresh();
    }

    public function reject(ContentDraft $draft, User $user, string $notes): ContentDraft
    {
        if ($draft->status !== ContentDraftStatus::PendingReview) {
            throw ValidationException::withMessages([
                'status' => __('app.content.errors.not_pending_review'),
            ]);
        }

        if (trim($notes) === '') {
            throw ValidationException::withMessages([
                'review_notes' => __('app.content.errors.rejection_notes_required'),
            ]);
        }

        $draft->update([
            'status' => ContentDraftStatus::Rejected,
            'reviewed_by' => $user->id,
            'reviewed_at' => now(),
            'review_notes' => $notes,
        ]);

        return $draft->refresh();
    }
}
