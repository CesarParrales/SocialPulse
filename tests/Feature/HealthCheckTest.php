<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class HealthCheckTest extends TestCase
{
    use RefreshDatabase;

    public function test_laravel_up_endpoint_returns_ok(): void
    {
        $this->get('/up')->assertOk();
    }

    public function test_health_endpoint_returns_ok_with_checks(): void
    {
        $this->getJson(route('health'))
            ->assertOk()
            ->assertJsonStructure([
                'status',
                'checks' => ['database', 'redis'],
                'app',
                'environment',
                'version',
                'timestamp',
            ])
            ->assertJsonPath('status', 'ok')
            ->assertJsonPath('checks.database.status', 'ok');
    }

    public function test_health_endpoint_returns_degraded_when_database_fails(): void
    {
        DB::shouldReceive('connection')->andThrow(new \RuntimeException('db down'));

        $this->getJson(route('health'))
            ->assertStatus(503)
            ->assertJsonPath('status', 'degraded')
            ->assertJsonPath('checks.database.status', 'failed');
    }
}
