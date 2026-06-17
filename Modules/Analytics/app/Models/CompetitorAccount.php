<?php

namespace Modules\Analytics\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Modules\Workspaces\Models\Workspace;

class CompetitorAccount extends Model
{
    protected $fillable = [
        'workspace_id',
        'name',
        'platform',
        'handle',
        'followers_count',
        'avg_reach',
        'avg_engagement_rate',
        'notes',
        'data_source_note',
        'is_active',
        'sort_order',
    ];

    protected function casts(): array
    {
        return [
            'followers_count' => 'integer',
            'avg_reach' => 'float',
            'avg_engagement_rate' => 'float',
            'is_active' => 'boolean',
            'sort_order' => 'integer',
        ];
    }

    public function workspace(): BelongsTo
    {
        return $this->belongsTo(Workspace::class);
    }

    /**
     * @return array<string, mixed>
     */
    public function toBenchmarkRow(): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'platform' => $this->platform,
            'handle' => $this->handle,
            'followers_count' => $this->followers_count,
            'avg_reach' => $this->avg_reach,
            'avg_engagement_rate' => $this->avg_engagement_rate,
            'notes' => $this->notes,
            'data_source_note' => $this->data_source_note,
            'source' => 'manual',
        ];
    }
}
