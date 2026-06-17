<?php

namespace Tests\Feature;

use Tests\TestCase;

class ScheduleRegistrationTest extends TestCase
{
    public function test_ingestion_and_horizon_jobs_are_scheduled(): void
    {
        $this->artisan('schedule:list')
            ->assertSuccessful()
            ->expectsOutputToContain('ingestion:organic-facebook-daily')
            ->expectsOutputToContain('ingestion:stories-watcher')
            ->expectsOutputToContain('connections:token-refresh')
            ->expectsOutputToContain('horizon:snapshot');
    }
}
