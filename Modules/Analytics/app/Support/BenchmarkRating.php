<?php

namespace Modules\Analytics\Support;

enum BenchmarkStatus: string
{
    case Good = 'good';
    case Normal = 'normal';
    case Poor = 'poor';
    case Insufficient = 'insufficient';

    public function label(): string
    {
        return match ($this) {
            self::Good => __('app.benchmarks.status_good'),
            self::Normal => __('app.benchmarks.status_normal'),
            self::Poor => __('app.benchmarks.status_poor'),
            self::Insufficient => __('app.benchmarks.status_insufficient'),
        };
    }
}

class BenchmarkRating
{
    /**
     * @return array{status: string, label: string, ratio_pct: float|null, current: float, baseline: float}
     */
    public static function rate(float $current, float $baseline, bool $higherIsBetter = true): array
    {
        if ($baseline <= 0 && $current <= 0) {
            return self::result(BenchmarkStatus::Insufficient, $current, $baseline, null);
        }

        if ($baseline <= 0) {
            return self::result(BenchmarkStatus::Good, $current, $baseline, 100.0);
        }

        $ratioPct = round(($current / $baseline) * 100, 1);

        if ($higherIsBetter) {
            $status = match (true) {
                $ratioPct >= 100 => BenchmarkStatus::Good,
                $ratioPct >= 85 => BenchmarkStatus::Normal,
                default => BenchmarkStatus::Poor,
            };
        } else {
            $status = match (true) {
                $ratioPct <= 100 => BenchmarkStatus::Good,
                $ratioPct <= 115 => BenchmarkStatus::Normal,
                default => BenchmarkStatus::Poor,
            };
        }

        return self::result($status, $current, $baseline, $ratioPct);
    }

    /**
     * @return array{status: string, label: string, ratio_pct: float|null, current: float, baseline: float}
     */
    private static function result(
        BenchmarkStatus $status,
        float $current,
        float $baseline,
        ?float $ratioPct,
    ): array {
        return [
            'status' => $status->value,
            'label' => $status->label(),
            'ratio_pct' => $ratioPct,
            'current' => $current,
            'baseline' => $baseline,
        ];
    }
}
