<?php

namespace Modules\Dashboard\Support;

class MetricComparison
{
    /**
     * @return array{current: float|int, previous: float|int, change_pct: float|null, direction: string, comparable: bool}
     */
    public static function compare(float|int $current, float|int $previous, bool $comparable = true): array
    {
        $direction = 'flat';

        if ($current > $previous) {
            $direction = 'up';
        } elseif ($current < $previous) {
            $direction = 'down';
        }

        $changePct = null;

        if ($comparable && $previous != 0) {
            $changePct = round((($current - $previous) / $previous) * 100, 1);
        } elseif ($comparable && $previous == 0 && $current != 0) {
            $changePct = 100.0;
        } elseif ($comparable && $previous == 0 && $current == 0) {
            $changePct = 0.0;
        }

        return [
            'current' => $current,
            'previous' => $previous,
            'change_pct' => $changePct,
            'direction' => $direction,
            'comparable' => $comparable,
        ];
    }
}
