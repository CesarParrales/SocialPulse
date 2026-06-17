<?php

use Illuminate\Support\Facades\Route;
use Modules\Reports\Http\Controllers\WorkspaceReportController;

Route::middleware(['auth', 'verified'])->group(function () {
    Route::prefix('workspaces/{workspace}')->group(function () {
        Route::get('reports', [WorkspaceReportController::class, 'index'])
            ->name('workspaces.reports.index');

        Route::get('reports/create', [WorkspaceReportController::class, 'create'])
            ->name('workspaces.reports.create');

        Route::post('reports', [WorkspaceReportController::class, 'store'])
            ->name('workspaces.reports.store');

        Route::get('reports/{report}', [WorkspaceReportController::class, 'show'])
            ->name('workspaces.reports.show');

        Route::get('reports/{report}/download', [WorkspaceReportController::class, 'download'])
            ->name('workspaces.reports.download');

        Route::get('reports/{report}/appendix', [WorkspaceReportController::class, 'downloadAppendix'])
            ->name('workspaces.reports.appendix');

        Route::get('reports/{report}/appendix/excel', [WorkspaceReportController::class, 'downloadAppendixExcel'])
            ->name('workspaces.reports.appendix.excel');

        Route::get('reports/{report}/preview', [WorkspaceReportController::class, 'preview'])
            ->name('workspaces.reports.preview');
    });
});
