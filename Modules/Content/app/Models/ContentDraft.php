<?php

namespace Modules\Content\Models;

use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Modules\Content\Enums\ContentChannel;
use Modules\Content\Enums\ContentDraftStatus;
use Modules\Content\Enums\ContentType;
use Modules\Workspaces\Models\Workspace;

class ContentDraft extends Model
{
    protected $fillable = [
        'workspace_id',
        'calendar_entry_id',
        'title',
        'caption',
        'channel',
        'content_type',
        'status',
        'review_notes',
        'media_url',
        'created_by',
        'reviewed_by',
        'submitted_at',
        'reviewed_at',
        'scheduled_at',
        'platform_post_id',
        'published_to_platform_at',
        'publish_error',
    ];

    protected function casts(): array
    {
        return [
            'channel' => ContentChannel::class,
            'content_type' => ContentType::class,
            'status' => ContentDraftStatus::class,
            'submitted_at' => 'datetime',
            'reviewed_at' => 'datetime',
            'scheduled_at' => 'datetime',
            'published_to_platform_at' => 'datetime',
        ];
    }

    public function publishedLink(): HasOne
    {
        return $this->hasOne(PublishedContentLink::class, 'content_draft_id');
    }

    public function workspace(): BelongsTo
    {
        return $this->belongsTo(Workspace::class);
    }

    public function calendarEntry(): BelongsTo
    {
        return $this->belongsTo(ContentCalendarEntry::class, 'calendar_entry_id');
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function reviewer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'reviewed_by');
    }

    /**
     * @return array<string, mixed>
     */
    public function toFrontend(): array
    {
        return [
            'id' => $this->id,
            'calendar_entry_id' => $this->calendar_entry_id,
            'title' => $this->title,
            'caption' => $this->caption,
            'channel' => $this->channel->value,
            'channel_label' => $this->channel->label(),
            'content_type' => $this->content_type->value,
            'content_type_label' => $this->content_type->label(),
            'status' => $this->status->value,
            'status_label' => $this->status->label(),
            'review_notes' => $this->review_notes,
            'scheduled_at' => $this->scheduled_at?->toIso8601String(),
            'scheduled_date' => $this->scheduled_at?->toDateString(),
            'submitted_at' => $this->submitted_at?->toIso8601String(),
            'reviewed_at' => $this->reviewed_at?->toIso8601String(),
            'is_editable' => $this->status->isEditable(),
            'can_publish' => $this->status === ContentDraftStatus::Approved,
            'media_url' => $this->media_url,
            'platform_post_id' => $this->platform_post_id,
            'published_to_platform_at' => $this->published_to_platform_at?->toIso8601String(),
            'publish_error' => $this->publish_error,
            'platform_permalink' => $this->relationLoaded('publishedLink')
                ? $this->publishedLink?->platform_permalink
                : null,
        ];
    }
}
