<?php

namespace Modules\Ingestion\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class OrganicPostMetricEntry extends Model
{
    protected $fillable = [
        'organic_post_id',
        'captured_at',
        'metrics',
    ];

    protected function casts(): array
    {
        return [
            'captured_at' => 'datetime',
            'metrics' => 'array',
        ];
    }

    public function post(): BelongsTo
    {
        return $this->belongsTo(OrganicPost::class, 'organic_post_id');
    }
}
