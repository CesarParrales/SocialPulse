<?php

namespace Modules\Workspaces\Enums;

enum AgencyPlan: string
{
    case Starter = 'starter';
    case Agency = 'agency';
    case AgencyPro = 'agency_pro';
    case Enterprise = 'enterprise';
}
