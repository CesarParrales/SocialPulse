<?php

use Illuminate\Support\Facades\Route;
use Modules\Ingestion\Http\Controllers\IngestionController;

Route::middleware(['auth', 'verified'])->group(function () {
    Route::resource('ingestions', IngestionController::class)->names('ingestion');
});
