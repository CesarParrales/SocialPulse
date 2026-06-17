<?php

namespace Modules\Analytics\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Modules\Connections\Models\ConnectedAsset;
use Modules\Workspaces\Models\Workspace;

class BenchmarkSnapshot extends Model
{
    protected $fillable = [
        'workspace_id',
        'asset_id',
        'period_start',
        'period_end',
        'engagement_rate_avg',
        'reach_avg',
        'cpm_avg',
        'calculated_at',
    ];

    protected function casts(): array
    {
        return [
            'period_start' => 'date',
            'period_end' => 'date',
            'engagement_rate_avg' => 'decimal:4',
            'reach_avg' => 'decimal:4',
            'cpm_avg' => 'decimal:4',
            'calculated_at' => 'datetime',
        ];
    }

    public function workspace(): BelongsTo
    {
        return $this->belongsTo(Workspace::class);
    }

    public function asset(): BelongsTo
    {
        return $this->belongsTo(ConnectedAsset::class, 'asset_id');
    }
}
