<?php

namespace Modules\Content\Enums;

enum ContentChannel: string
{
    case Facebook = 'facebook';
    case Instagram = 'instagram';

    public function label(): string
    {
        return __('app.content.channels.'.$this->value);
    }
}
