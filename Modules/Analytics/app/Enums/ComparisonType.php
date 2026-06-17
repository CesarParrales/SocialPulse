<?php

namespace Modules\Analytics\Enums;

enum ComparisonType: string
{
    case PeriodVsPeriod = 'period_vs_period';
    case MonthVsPrevious = 'month_vs_previous';
    case QuarterVsPrevious = 'quarter_vs_previous';
    case OrganicVsPaid = 'organic_vs_paid';
    case FacebookVsInstagram = 'facebook_vs_instagram';
    case ContentTypes = 'content_types';

    public function label(): string
    {
        return match ($this) {
            self::PeriodVsPeriod => __('app.compare.types.period_vs_period'),
            self::MonthVsPrevious => __('app.compare.types.month_vs_previous'),
            self::QuarterVsPrevious => __('app.compare.types.quarter_vs_previous'),
            self::OrganicVsPaid => __('app.compare.types.organic_vs_paid'),
            self::FacebookVsInstagram => __('app.compare.types.facebook_vs_instagram'),
            self::ContentTypes => __('app.compare.types.content_types'),
        };
    }
}
