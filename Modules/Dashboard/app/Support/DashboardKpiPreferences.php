<?php

namespace Modules\Dashboard\Support;

class DashboardKpiPreferences
{
    /** @var list<string> */
    public const METRICS = [
        'reach',
        'impressions',
        'engagement_rate',
        'spend',
        'follower_growth',
        'posts_published',
    ];

    /**
     * @param  array<string, mixed>|null  $settings
     * @return list<string>
     */
    public static function visibleFromSettings(?array $settings): array
    {
        $stored = $settings['dashboard']['visible_kpis'] ?? null;

        if (! is_array($stored)) {
            return self::METRICS;
        }

        return self::normalize($stored);
    }

    /**
     * @param  array<string, mixed>|null  $settings
     * @param  list<string>  $visibleKpis
     * @return array<string, mixed>
     */
    public static function mergeIntoSettings(?array $settings, array $visibleKpis): array
    {
        $settings ??= [];

        $settings['dashboard'] = [
            ...($settings['dashboard'] ?? []),
            'visible_kpis' => self::normalize($visibleKpis),
        ];

        return $settings;
    }

    /**
     * @param  list<mixed>  $input
     * @return list<string>
     */
    public static function normalize(array $input): array
    {
        $metrics = collect($input)
            ->filter(fn ($metric) => is_string($metric) && in_array($metric, self::METRICS, true))
            ->values()
            ->all();

        return $metrics === [] ? self::METRICS : $metrics;
    }
}
