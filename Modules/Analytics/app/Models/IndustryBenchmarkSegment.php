<?php

namespace Modules\Analytics\Models;

use Illuminate\Database\Eloquent\Model;

class IndustryBenchmarkSegment extends Model
{
    public const MIN_SAMPLE_SIZE = 30;

    protected $fillable = [
        'industry_category',
        'community_size_band',
        'region',
        'sample_size',
        'engagement_rate_avg',
        'reach_avg',
        'cpm_avg',
        'calculated_at',
    ];

    protected function casts(): array
    {
        return [
            'engagement_rate_avg' => 'decimal:4',
            'reach_avg' => 'decimal:4',
            'cpm_avg' => 'decimal:4',
            'calculated_at' => 'datetime',
        ];
    }

    public function isRepresentative(): bool
    {
        return $this->sample_size >= self::MIN_SAMPLE_SIZE;
    }
}
