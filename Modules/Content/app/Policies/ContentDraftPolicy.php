<?php

namespace Modules\Content\Policies;

use App\Models\User;
use Modules\Content\Enums\ContentDraftStatus;
use Modules\Content\Models\ContentDraft;
use Modules\Workspaces\Models\Workspace;

class ContentDraftPolicy
{
    public function viewAny(User $user, Workspace $workspace): bool
    {
        return $user->canAccessWorkspace($workspace);
    }

    public function create(User $user, Workspace $workspace): bool
    {
        return $user->canAccessWorkspace($workspace) && ! $user->isClientReadonly();
    }

    public function update(User $user, ContentDraft $draft): bool
    {
        if (! $user->canAccessWorkspace($draft->workspace) || $user->isClientReadonly()) {
            return false;
        }

        return $draft->status->isEditable();
    }

    public function submit(User $user, ContentDraft $draft): bool
    {
        return $this->update($user, $draft);
    }

    public function review(User $user, ContentDraft $draft): bool
    {
        if (! $user->canAccessWorkspace($draft->workspace)) {
            return false;
        }

        if ($draft->status !== ContentDraftStatus::PendingReview) {
            return false;
        }

        return $user->isClientReadonly()
            || $user->isAgencyAdmin()
            || $user->isSuperAdmin();
    }

    public function publish(User $user, ContentDraft $draft): bool
    {
        if (! $user->canAccessWorkspace($draft->workspace) || $user->isClientReadonly()) {
            return false;
        }

        return $draft->status === ContentDraftStatus::Approved;
    }
}
