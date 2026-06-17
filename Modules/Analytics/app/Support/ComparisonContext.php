<?php

namespace Modules\Analytics\Support;

use Carbon\Carbon;
use Illuminate\Http\Request;
use InvalidArgumentException;
use Modules\Analytics\Enums\ComparisonType;

class ComparisonContext
{
    public function __construct(
        public readonly ComparisonType $type,
        public readonly Carbon $leftStart,
        public readonly Carbon $leftEnd,
        public readonly Carbon $rightStart,
        public readonly Carbon $rightEnd,
        public readonly string $leftLabel,
        public readonly string $rightLabel,
    ) {}

    public static function fromRequest(Request $request): self
    {
        $type = ComparisonType::tryFrom($request->string('type', ComparisonType::OrganicVsPaid->value));

        if ($type === null) {
            throw new InvalidArgumentException('Invalid comparison type.');
        }

        return match ($type) {
            ComparisonType::PeriodVsPeriod => self::customPeriods($request),
            ComparisonType::MonthVsPrevious => self::monthVsPrevious(),
            ComparisonType::QuarterVsPrevious => self::quarterVsPrevious(),
            ComparisonType::OrganicVsPaid,
            ComparisonType::FacebookVsInstagram,
            ComparisonType::ContentTypes => self::sharedPeriod($request, $type),
        };
    }

    /**
     * @return array{left_start: string, left_end: string, right_start: string, right_end: string, type: string}
     */
    public function toFilters(): array
    {
        return [
            'type' => $this->type->value,
            'left_start' => $this->leftStart->toDateString(),
            'left_end' => $this->leftEnd->toDateString(),
            'right_start' => $this->rightStart->toDateString(),
            'right_end' => $this->rightEnd->toDateString(),
        ];
    }

    private static function customPeriods(Request $request): self
    {
        $leftStart = Carbon::parse($request->string('left_start')->value())->startOfDay();
        $leftEnd = Carbon::parse($request->string('left_end')->value())->endOfDay();
        $rightStart = Carbon::parse($request->string('right_start')->value())->startOfDay();
        $rightEnd = Carbon::parse($request->string('right_end')->value())->endOfDay();

        return new self(
            ComparisonType::PeriodVsPeriod,
            $leftStart,
            $leftEnd,
            $rightStart,
            $rightEnd,
            "Período A ({$leftStart->toDateString()} — {$leftEnd->toDateString()})",
            "Período B ({$rightStart->toDateString()} — {$rightEnd->toDateString()})",
        );
    }

    private static function monthVsPrevious(): self
    {
        $leftStart = now()->startOfMonth();
        $leftEnd = now()->endOfDay();
        $rightStart = now()->subMonthNoOverflow()->startOfMonth();
        $rightEnd = now()->subMonthNoOverflow()->endOfMonth();

        return new self(
            ComparisonType::MonthVsPrevious,
            $leftStart,
            $leftEnd,
            $rightStart,
            $rightEnd,
            'Mes actual',
            'Mes anterior',
        );
    }

    private static function quarterVsPrevious(): self
    {
        $leftStart = now()->firstOfQuarter();
        $leftEnd = now()->endOfDay();
        $rightStart = now()->subQuarter()->firstOfQuarter();
        $rightEnd = now()->subQuarter()->lastOfQuarter();

        return new self(
            ComparisonType::QuarterVsPrevious,
            $leftStart,
            $leftEnd,
            $rightStart,
            $rightEnd,
            'Trimestre actual',
            'Trimestre anterior',
        );
    }

    private static function sharedPeriod(Request $request, ComparisonType $type): self
    {
        $days = match ($request->string('period', '30d')->value()) {
            '7d' => 7,
            '14d' => 14,
            '90d' => 90,
            default => 30,
        };

        $start = now()->subDays($days - 1)->startOfDay();
        $end = now()->endOfDay();

        return new self(
            $type,
            $start,
            $end,
            $start,
            $end,
            "Período ({$start->toDateString()} — {$end->toDateString()})",
            "Período ({$start->toDateString()} — {$end->toDateString()})",
        );
    }
}
