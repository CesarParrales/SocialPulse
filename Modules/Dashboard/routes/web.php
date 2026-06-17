<?php

use Illuminate\Support\Facades\Route;
use Modules\Dashboard\Http\Controllers\PublicDashboardController;
use Modules\Dashboard\Http\Controllers\WorkspaceDashboardController;
use Modules\Dashboard\Http\Controllers\WorkspacePublicDashboardController;

Route::middleware(['throttle:60,1'])->group(function () {
    Route::get('view/{token}', [PublicDashboardController::class, 'show'])
        ->where('token', '[a-zA-Z0-9]{48,64}')
        ->name('public.dashboard');
});

Route::middleware(['auth', 'verified'])->group(function () {
    Route::get('workspaces/{workspace}/dashboard', [WorkspaceDashboardController::class, 'show'])
        ->name('workspaces.dashboard');

    Route::put('workspaces/{workspace}/dashboard/kpi-preferences', [WorkspaceDashboardController::class, 'updateKpiPreferences'])
        ->name('workspaces.dashboard.kpi-preferences');

    Route::post('workspaces/{workspace}/public-dashboard/enable', [WorkspacePublicDashboardController::class, 'enable'])
        ->name('workspaces.public-dashboard.enable');

    Route::post('workspaces/{workspace}/public-dashboard/disable', [WorkspacePublicDashboardController::class, 'disable'])
        ->name('workspaces.public-dashboard.disable');

    Route::post('workspaces/{workspace}/public-dashboard/regenerate', [WorkspacePublicDashboardController::class, 'regenerate'])
        ->name('workspaces.public-dashboard.regenerate');
});
