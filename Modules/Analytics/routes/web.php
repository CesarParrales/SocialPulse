<?php

use Illuminate\Support\Facades\Route;
use Modules\Analytics\Http\Controllers\WorkspaceBenchmarkController;
use Modules\Analytics\Http\Controllers\WorkspaceComparisonController;
use Modules\Analytics\Http\Controllers\WorkspaceCompetitorController;

Route::middleware(['auth', 'verified'])->group(function () {
    Route::get('workspaces/{workspace}/compare', [WorkspaceComparisonController::class, 'show'])
        ->name('workspaces.compare');

    Route::get('workspaces/{workspace}/benchmarks', [WorkspaceBenchmarkController::class, 'show'])
        ->name('workspaces.benchmarks');

    Route::get('workspaces/{workspace}/competitors', [WorkspaceCompetitorController::class, 'index'])
        ->name('workspaces.competitors.index');
    Route::post('workspaces/{workspace}/competitors/prompt', [WorkspaceCompetitorController::class, 'generatePrompt'])
        ->name('workspaces.competitors.prompt');
    Route::put('workspaces/{workspace}/competitors/insight', [WorkspaceCompetitorController::class, 'saveInsight'])
        ->name('workspaces.competitors.insight');
    Route::post('workspaces/{workspace}/competitors', [WorkspaceCompetitorController::class, 'store'])
        ->name('workspaces.competitors.store');
    Route::put('workspaces/{workspace}/competitors/{competitor}', [WorkspaceCompetitorController::class, 'update'])
        ->name('workspaces.competitors.update');
    Route::delete('workspaces/{workspace}/competitors/{competitor}', [WorkspaceCompetitorController::class, 'destroy'])
        ->name('workspaces.competitors.destroy');
});
