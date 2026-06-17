<?php

namespace Modules\Dashboard\Tests\Unit;

use Modules\Dashboard\Support\OrganicMetricResolver;
use PHPUnit\Framework\TestCase;

class OrganicMetricResolverTest extends TestCase
{
    public function test_tiktok_views_map_to_reach_and_impressions(): void
    {
        $metrics = [
            'views' => 12000,
            'likes' => 850,
            'comments' => 42,
            'shares' => 18,
        ];

        $this->assertSame(12000.0, OrganicMetricResolver::reach($metrics));
        $this->assertSame(12000.0, OrganicMetricResolver::impressions($metrics));
        $this->assertSame(910.0, OrganicMetricResolver::engagement($metrics));
    }
}
