<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Modules\Workspaces\Database\Seeders\DemoSeeder;
use Modules\Workspaces\Database\Seeders\RolesSeeder;
use Tests\TestCase;

class SocialPulseSmokeCommandTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed(RolesSeeder::class);
    }

    public function test_smoke_command_passes_public_routes(): void
    {
        $this->withoutVite();

        $this->artisan('socialpulse:smoke')
            ->assertSuccessful();
    }

    public function test_smoke_command_with_auth_passes_after_demo_seed(): void
    {
        $this->withoutVite();
        $this->seed(DemoSeeder::class);

        $this->artisan('socialpulse:smoke', ['--auth' => true])
            ->assertSuccessful();
    }
}
