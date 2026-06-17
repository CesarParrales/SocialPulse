<?php

namespace Modules\Reports\Support;

class ReportConfig
{
    /** @var list<string> */
    public const SECTIONS = [
        'overview',
        'organic',
        'paid',
        'top_content',
        'comparisons',
        'competitors',
    ];

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
     * @param  array<string, mixed>  $input
     * @return array<string, mixed>
     */
    public static function normalize(array $input): array
    {
        $sections = collect(self::SECTIONS)
            ->mapWithKeys(fn (string $section) => [
                $section => (bool) ($input['sections'][$section] ?? true),
            ])
            ->all();

        $metrics = collect(self::METRICS)
            ->filter(fn (string $metric) => (bool) ($input['metrics'][$metric] ?? true))
            ->values()
            ->all();

        if ($metrics === []) {
            $metrics = self::METRICS;
        }

        return [
            'title' => (string) ($input['title'] ?? 'Reporte de rendimiento'),
            'primary_color' => self::normalizeColor($input['primary_color'] ?? '#4f46e5'),
            'secondary_color' => self::normalizeColor($input['secondary_color'] ?? '#818cf8'),
            'logo_path' => isset($input['logo_path']) ? (string) $input['logo_path'] : null,
            'sections' => $sections,
            'metrics' => $metrics,
        ];
    }

    private static function normalizeColor(mixed $color): string
    {
        $value = strtolower(trim((string) $color));

        if (! preg_match('/^#[0-9a-f]{6}$/', $value)) {
            return '#4f46e5';
        }

        return $value;
    }
}
