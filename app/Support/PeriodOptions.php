<?php

namespace App\Support;

class PeriodOptions
{
    /**
     * @return list<array{value: string, label: string}>
     */
    public static function presets(bool $includeCustom = false): array
    {
        $options = [
            ['value' => '7d', 'label' => __('app.periods.7d')],
            ['value' => '14d', 'label' => __('app.periods.14d')],
            ['value' => '30d', 'label' => __('app.periods.30d')],
            ['value' => '90d', 'label' => __('app.periods.90d')],
        ];

        if ($includeCustom) {
            $options[] = ['value' => 'custom', 'label' => __('app.periods.custom')];
        }

        return $options;
    }
}
