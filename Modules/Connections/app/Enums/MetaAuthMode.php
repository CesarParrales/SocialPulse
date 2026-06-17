<?php

namespace Modules\Connections\Enums;

enum MetaAuthMode: string
{
    case UserOAuth = 'user_oauth';
    case SystemUser = 'system_user';
}
