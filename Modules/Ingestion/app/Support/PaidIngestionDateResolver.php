<?php

namespace Modules\Ingestion\Support;

use Carbon\Carbon;
use Modules\Connections\Models\ConnectedAsset;

class PaidIngestionDateResolver
{
    public function resolve(bool $preliminary): Carbon
    {
        return $preliminary ? now()->startOfDay() : now()->subDay()->startOfDay();
    }

    public function resolveRange(bool $preliminary): array
    {
        $date = $this->resolve($preliminary);

        return ['since' => $date, 'until' => $date->copy()];
    }

    public function connectionAccessToken(ConnectedAsset $asset): string
    {
        $token = $asset->connection?->access_token;

        if (! is_string($token) || $token === '') {
            throw new \RuntimeException('Missing access token for paid asset connection.');
        }

        return $token;
    }
}
