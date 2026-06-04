<?php

namespace Modules\Connections\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Modules\Connections\Enums\AssetType;

class ConnectedAsset extends Model
{
    protected $fillable = [
        'connection_id',
        'asset_type',
        'platform_asset_id',
        'name',
        'is_active',
        'metadata',
    ];

    protected function casts(): array
    {
        return [
            'asset_type' => AssetType::class,
            'is_active' => 'boolean',
            'metadata' => 'array',
        ];
    }

    public function connection(): BelongsTo
    {
        return $this->belongsTo(PlatformConnection::class, 'connection_id');
    }
}
