<?php

namespace Modules\Ingestion\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Modules\Connections\Models\ConnectedAsset;

class StorySnapshot extends Model
{
    protected $table = 'stories_snapshots';

    protected $fillable = [
        'asset_id',
        'story_id',
        'captured_at',
        'reach',
        'impressions',
        'taps_forward',
        'taps_back',
        'exits',
        'replies',
        'expires_at',
        'is_expired',
    ];

    protected function casts(): array
    {
        return [
            'captured_at' => 'datetime',
            'expires_at' => 'datetime',
            'is_expired' => 'boolean',
        ];
    }

    public function asset(): BelongsTo
    {
        return $this->belongsTo(ConnectedAsset::class, 'asset_id');
    }
}
