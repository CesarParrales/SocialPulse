<?php

namespace Modules\Dashboard\Support;

use Carbon\Carbon;
use Illuminate\Http\Request;
use InvalidArgumentException;

class DashboardPeriod
{
    /** @var list<string> */
    public const PRESETS = ['7d', '14d', '30d', '90d', 'custom'];

    public function __construct(
        public readonly string $preset,
        public readonly Carbon $start,
        public readonly Carbon $end,
        public readonly Carbon $previousStart,
        public readonly Carbon $previousEnd,
    ) {}

    public static function fromRequest(Request $request): self
    {
        $preset = $request->string('period', '30d')->value();

        return self::fromPreset($preset, $request->string('from')->value(), $request->string('to')->value());
    }

    public static function fromPreset(string $preset = '30d', ?string $from = null, ?string $to = null): self
    {
        if (! in_array($preset, self::PRESETS, true)) {
            throw new InvalidArgumentException('Invalid dashboard period preset.');
        }

        if ($preset === 'custom') {
            if ($from === null || $to === null || $from === '' || $to === '') {
                throw new InvalidArgumentException('Custom period requires from and to dates.');
            }

            $start = Carbon::parse($from)->startOfDay();
            $end = Carbon::parse($to)->endOfDay();

            if ($start->greaterThan($end)) {
                throw new InvalidArgumentException('Invalid custom date range.');
            }
        } else {
            $days = (int) rtrim($preset, 'd');
            $end = now()->endOfDay();
            $start = now()->subDays($days - 1)->startOfDay();
        }

        $dayCount = $start->copy()->startOfDay()->diffInDays($end->copy()->startOfDay()) + 1;
        $previousEnd = $start->copy()->subDay()->endOfDay();
        $previousStart = $previousEnd->copy()->subDays($dayCount - 1)->startOfDay();

        return new self($preset, $start, $end, $previousStart, $previousEnd);
    }

    public static function fromDates(Carbon $start, Carbon $end): self
    {
        $start = $start->copy()->startOfDay();
        $end = $end->copy()->endOfDay();

        if ($start->greaterThan($end)) {
            throw new InvalidArgumentException('Invalid date range.');
        }

        $dayCount = $start->diffInDays($end) + 1;
        $previousEnd = $start->copy()->subDay()->endOfDay();
        $previousStart = $previousEnd->copy()->subDays($dayCount - 1)->startOfDay();

        return new self('custom', $start, $end, $previousStart, $previousEnd);
    }

    public function days(): int
    {
        return $this->start->copy()->startOfDay()->diffInDays($this->end->copy()->startOfDay()) + 1;
    }

    /**
     * @return array<string, string>
     */
    public function toFilters(): array
    {
        return [
            'period' => $this->preset,
            'from' => $this->start->toDateString(),
            'to' => $this->end->toDateString(),
        ];
    }
}
