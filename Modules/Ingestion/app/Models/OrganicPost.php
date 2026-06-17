<?php

namespace Modules\Ingestion\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Modules\Connections\Models\ConnectedAsset;

class OrganicPost extends Model
{
    protected $fillable = [
        'asset_id',
        'platform_post_id',
        'post_type',
        'published_at',
        'content_preview',
        'thumbnail_url',
        'raw_metrics',
        'captured_at',
    ];

    protected function casts(): array
    {
        return [
            'published_at' => 'datetime',
            'raw_metrics' => 'array',
            'captured_at' => 'datetime',
        ];
    }

    public function asset(): BelongsTo
    {
        return $this->belongsTo(ConnectedAsset::class, 'asset_id');
    }

    public function metricEntries(): HasMany
    {
        return $this->hasMany(OrganicPostMetricEntry::class);
    }
}
