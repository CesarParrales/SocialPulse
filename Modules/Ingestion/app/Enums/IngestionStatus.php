<?php

namespace Modules\Ingestion\Enums;

enum IngestionStatus: string
{
    case Success = 'success';
    case Error = 'error';
    case Partial = 'partial';
}
