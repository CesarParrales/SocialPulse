<?php

namespace Modules\Content\Contracts;

use Modules\Connections\Models\ConnectedAsset;
use Modules\Content\Models\ContentDraft;
use Modules\Content\Support\MetaPublishResult;

interface MetaContentPublisher
{
    public function publish(ContentDraft $draft, ConnectedAsset $asset): MetaPublishResult;
}
