<?php

namespace Modules\Workspaces\Enums;

enum AgencyPlan: string
{
    case Starter = 'starter';
    case Agency = 'agency';
    case AgencyPro = 'agency_pro';
    case Enterprise = 'enterprise';

    public function label(): string
    {
        return match ($this) {
            self::Starter => __('app.platform.plans.starter'),
            self::Agency => __('app.platform.plans.agency'),
            self::AgencyPro => __('app.platform.plans.agency_pro'),
            self::Enterprise => __('app.platform.plans.enterprise'),
        };
    }
}
