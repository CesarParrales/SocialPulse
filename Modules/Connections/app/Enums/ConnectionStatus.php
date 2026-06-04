<?php

namespace Modules\Connections\Enums;

enum ConnectionStatus: string
{
    case Active = 'active';
    case Expired = 'expired';
    case Error = 'error';
}
