<?php

namespace Modules\Ingestion\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AdMetricDaily extends Model
{
    protected $table = 'ad_metrics_daily';

    protected $fillable = [
        'campaign_id',
        'ad_set_id',
        'ad_id',
        'date',
        'placement',
        'spend',
        'reach',
        'impressions',
        'clicks',
        'ctr',
        'cpm',
        'cpc',
        'conversions',
        'conversion_value',
        'roas',
        'is_preliminary',
        'captured_at',
    ];

    protected function casts(): array
    {
        return [
            'date' => 'date',
            'spend' => 'decimal:4',
            'ctr' => 'decimal:6',
            'cpm' => 'decimal:4',
            'cpc' => 'decimal:4',
            'conversions' => 'decimal:4',
            'conversion_value' => 'decimal:4',
            'roas' => 'decimal:4',
            'is_preliminary' => 'boolean',
            'captured_at' => 'datetime',
        ];
    }

    public function campaign(): BelongsTo
    {
        return $this->belongsTo(AdCampaign::class, 'campaign_id');
    }
}
