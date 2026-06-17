<?php

namespace Modules\Content\Enums;

enum ContentType: string
{
    case Feed = 'feed';
    case Reel = 'reel';

    public function label(): string
    {
        return __('app.content.types.'.$this->value);
    }
}
