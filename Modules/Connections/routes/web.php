<?php

use Illuminate\Support\Facades\Route;
use Modules\Connections\Http\Controllers\WorkspaceConnectionController;

Route::middleware(['auth', 'verified'])->group(function () {
    Route::get('workspaces/{workspace}/connections', [WorkspaceConnectionController::class, 'index'])
        ->name('workspaces.connections.index');

    Route::get('workspaces/{workspace}/connections/meta/redirect', [WorkspaceConnectionController::class, 'metaRedirect'])
        ->name('workspaces.connections.meta.redirect');

    Route::get('workspaces/{workspace}/connections/google/redirect', [WorkspaceConnectionController::class, 'googleRedirect'])
        ->name('workspaces.connections.google.redirect');

    Route::post('workspaces/{workspace}/connections/{connection}/assets', [WorkspaceConnectionController::class, 'syncAssets'])
        ->name('workspaces.connections.assets.sync');

    Route::delete('workspaces/{workspace}/connections/{connection}', [WorkspaceConnectionController::class, 'destroy'])
        ->name('workspaces.connections.destroy');

    Route::get('connections/meta/callback', [WorkspaceConnectionController::class, 'metaCallback'])
        ->name('connections.meta.callback');

    Route::get('connections/google/callback', [WorkspaceConnectionController::class, 'googleCallback'])
        ->name('connections.google.callback');
});
