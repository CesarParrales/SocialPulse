<?php

namespace Modules\Workspaces\Enums;

enum SystemRole: string
{
    case SuperAdmin = 'super_admin';
    case AgencyAdmin = 'agency_admin';
    case Operator = 'operator';
    case ClientReadonly = 'client_readonly';
}
