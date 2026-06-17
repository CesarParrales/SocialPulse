<?php

namespace Modules\Content\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Modules\Connections\Models\ConnectedAsset;
use Modules\Ingestion\Models\OrganicPost;

class PublishedContentLink extends Model
{
    protected $fillable = [
        'content_draft_id',
        'organic_post_id',
        'asset_id',
        'platform_post_id',
        'platform_permalink',
        'published_at',
    ];

    protected function casts(): array
    {
        return [
            'published_at' => 'datetime',
        ];
    }

    public function draft(): BelongsTo
    {
        return $this->belongsTo(ContentDraft::class, 'content_draft_id');
    }

    public function organicPost(): BelongsTo
    {
        return $this->belongsTo(OrganicPost::class, 'organic_post_id');
    }

    public function asset(): BelongsTo
    {
        return $this->belongsTo(ConnectedAsset::class, 'asset_id');
    }
}
