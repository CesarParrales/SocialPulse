<?php

namespace Modules\Analytics\Enums;

enum CommunitySizeBand: string
{
    case Lt10k = 'lt_10k';
    case From10kTo100k = '10k_100k';
    case From100kTo500k = '100k_500k';
    case Gte500k = '500k_plus';

    public static function fromFollowerCount(int $followers): self
    {
        return match (true) {
            $followers < 10_000 => self::Lt10k,
            $followers < 100_000 => self::From10kTo100k,
            $followers < 500_000 => self::From100kTo500k,
            default => self::Gte500k,
        };
    }

    public function label(): string
    {
        return match ($this) {
            self::Lt10k => __('app.benchmarks.community_size.lt_10k'),
            self::From10kTo100k => __('app.benchmarks.community_size.10k_100k'),
            self::From100kTo500k => __('app.benchmarks.community_size.100k_500k'),
            self::Gte500k => __('app.benchmarks.community_size.500k_plus'),
        };
    }
}
