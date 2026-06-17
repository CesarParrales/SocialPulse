<?php

namespace Modules\Content\Models;

use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Modules\Content\Enums\ContentChannel;
use Modules\Content\Enums\ContentType;
use Modules\Workspaces\Models\Workspace;

class ContentCalendarEntry extends Model
{
    protected $fillable = [
        'workspace_id',
        'title',
        'scheduled_at',
        'channel',
        'content_type',
        'created_by',
    ];

    protected function casts(): array
    {
        return [
            'scheduled_at' => 'datetime',
            'channel' => ContentChannel::class,
            'content_type' => ContentType::class,
        ];
    }

    public function workspace(): BelongsTo
    {
        return $this->belongsTo(Workspace::class);
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function draft(): HasOne
    {
        return $this->hasOne(ContentDraft::class, 'calendar_entry_id');
    }

    /**
     * @return array<string, mixed>
     */
    public function toFrontend(): array
    {
        return [
            'id' => $this->id,
            'title' => $this->title,
            'scheduled_at' => $this->scheduled_at?->toIso8601String(),
            'scheduled_date' => $this->scheduled_at?->toDateString(),
            'channel' => $this->channel->value,
            'channel_label' => $this->channel->label(),
            'content_type' => $this->content_type->value,
            'content_type_label' => $this->content_type->label(),
            'draft_id' => $this->draft?->id,
            'draft_status' => $this->draft?->status->value,
            'draft_status_label' => $this->draft?->status->label(),
        ];
    }
}
