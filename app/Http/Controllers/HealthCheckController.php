<?php

namespace App\Http\Controllers;

use App\Services\HealthCheckService;
use Illuminate\Http\JsonResponse;

class HealthCheckController extends Controller
{
    public function __invoke(HealthCheckService $health): JsonResponse
    {
        $report = $health->run();

        $statusCode = $report['status'] === 'ok' ? 200 : 503;

        return response()->json($report, $statusCode);
    }
}
