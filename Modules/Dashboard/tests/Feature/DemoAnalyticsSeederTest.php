<?php

namespace Modules\Dashboard\Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Modules\Connections\Models\ConnectedAsset;
use Modules\Ingestion\Models\OrganicPost;
use Modules\Workspaces\Database\Seeders\DemoSeeder;
use Modules\Workspaces\Database\Seeders\RolesSeeder;
use Tests\TestCase;

class DemoAnalyticsSeederTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed(RolesSeeder::class);
    }

    public function test_demo_seeder_creates_analytics_sample_data(): void
    {
        $this->seed(DemoSeeder::class);

        $this->assertGreaterThanOrEqual(3, ConnectedAsset::query()->count());
        $this->assertGreaterThanOrEqual(6, OrganicPost::query()->count());
    }
}
