<?php

namespace Modules\Content\Enums;

enum ContentDraftStatus: string
{
    case Draft = 'draft';
    case PendingReview = 'pending_review';
    case Approved = 'approved';
    case Rejected = 'rejected';
    case Published = 'published';
    case Cancelled = 'cancelled';

    public function label(): string
    {
        return __('app.content.status.'.$this->value);
    }

    public function isEditable(): bool
    {
        return in_array($this, [self::Draft, self::Rejected], true);
    }
}
