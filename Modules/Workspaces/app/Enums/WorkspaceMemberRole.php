<?php

namespace Modules\Workspaces\Enums;

enum WorkspaceMemberRole: string
{
    case Operator = 'operator';
    case ClientReadonly = 'client_readonly';
}
