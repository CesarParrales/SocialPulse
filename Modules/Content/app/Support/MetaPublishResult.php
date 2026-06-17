<?php

namespace Modules\Content\Support;

readonly class MetaPublishResult
{
    public function __construct(
        public string $platformPostId,
        public ?string $permalink = null,
    ) {}
}
