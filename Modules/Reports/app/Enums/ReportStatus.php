<?php

namespace Modules\Reports\Enums;

enum ReportStatus: string
{
    case Pending = 'pending';
    case Generating = 'generating';
    case Ready = 'ready';
    case Error = 'error';

    public function label(): string
    {
        return match ($this) {
            self::Pending => __('app.reports.status.pending'),
            self::Generating => __('app.reports.status.generating'),
            self::Ready => __('app.reports.status.ready'),
            self::Error => __('app.reports.status.error'),
        };
    }
}
