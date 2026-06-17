<?php

namespace Modules\Dashboard\Support;

class OrganicMetricResolver
{
    /**
     * @param  array<string, mixed>  $metrics
     */
    public static function reach(array $metrics): float
    {
        return (float) ($metrics['reach'] ?? $metrics['views'] ?? 0);
    }

    /**
     * @param  array<string, mixed>  $metrics
     */
    public static function impressions(array $metrics): float
    {
        return (float) ($metrics['impressions'] ?? $metrics['views'] ?? 0);
    }

    /**
     * @param  array<string, mixed>  $metrics
     */
    public static function engagement(array $metrics): float
    {
        if (isset($metrics['engagement']) && is_numeric($metrics['engagement'])) {
            return (float) $metrics['engagement'];
        }

        return (float) ($metrics['likes'] ?? 0)
            + (float) ($metrics['comments'] ?? 0)
            + (float) ($metrics['shares'] ?? 0)
            + (float) (is_numeric($metrics['reactions'] ?? null) ? $metrics['reactions'] : 0);
    }

    /**
     * @param  array<string, mixed>  $metrics
     */
    public static function interactions(array $metrics): float
    {
        return (float) ($metrics['likes'] ?? 0)
            + (float) ($metrics['comments'] ?? 0)
            + (float) ($metrics['shares'] ?? 0)
            + (float) (is_numeric($metrics['reactions'] ?? null) ? $metrics['reactions'] : 0);
    }
}
