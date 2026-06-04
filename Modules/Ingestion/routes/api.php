<?php

use Illuminate\Support\Facades\Route;
use Modules\Ingestion\Http\Controllers\IngestionController;

Route::middleware(['auth:sanctum'])->prefix('v1')->group(function () {
    Route::apiResource('ingestions', IngestionController::class)->names('ingestion');
});
