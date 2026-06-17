<?php

namespace App\Services;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Redis;
use Throwable;

class HealthCheckService
{
    /**
     * @return array<string, mixed>
     */
    public function run(): array
    {
        $checks = [
            'database' => $this->checkDatabase(),
            'redis' => $this->checkRedis(),
        ];

        $healthy = collect($checks)->every(
            fn (array $check) => in_array($check['status'], ['ok', 'skipped'], true),
        );

        return [
            'status' => $healthy ? 'ok' : 'degraded',
            'checks' => $checks,
            'app' => config('app.name'),
            'environment' => config('app.env'),
            'version' => config('app.version'),
            'timestamp' => now()->toIso8601String(),
        ];
    }

    /**
     * @return array{status: string, message?: string}
     */
    private function checkDatabase(): array
    {
        try {
            DB::connection()->getPdo();

            return ['status' => 'ok'];
        } catch (Throwable $exception) {
            return [
                'status' => 'failed',
                'message' => 'Database unreachable',
            ];
        }
    }

    /**
     * @return array{status: string, message?: string}
     */
    private function checkRedis(): array
    {
        if (! $this->usesRedis()) {
            return [
                'status' => 'skipped',
                'message' => 'Redis not configured for this environment',
            ];
        }

        try {
            Redis::connection()->ping();

            return ['status' => 'ok'];
        } catch (Throwable $exception) {
            return [
                'status' => 'failed',
                'message' => 'Redis unreachable',
            ];
        }
    }

    private function usesRedis(): bool
    {
        $drivers = [
            config('cache.default'),
            config('session.driver'),
            config('queue.default'),
        ];

        return in_array('redis', $drivers, true);
    }
}
