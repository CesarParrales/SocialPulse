<?php

use Illuminate\Support\Facades\Route;
use Modules\Content\Http\Controllers\WorkspaceContentController;

Route::middleware(['auth', 'verified'])->group(function () {
    Route::get('workspaces/{workspace}/content', [WorkspaceContentController::class, 'index'])
        ->name('workspaces.content.index');

    Route::post('workspaces/{workspace}/content/entries', [WorkspaceContentController::class, 'storeEntry'])
        ->name('workspaces.content.entries.store');

    Route::post('workspaces/{workspace}/content/drafts', [WorkspaceContentController::class, 'storeDraft'])
        ->name('workspaces.content.drafts.store');

    Route::put('workspaces/{workspace}/content/drafts/{draft}', [WorkspaceContentController::class, 'updateDraft'])
        ->name('workspaces.content.drafts.update');

    Route::post('workspaces/{workspace}/content/drafts/{draft}/submit', [WorkspaceContentController::class, 'submitDraft'])
        ->name('workspaces.content.drafts.submit');

    Route::post('workspaces/{workspace}/content/drafts/{draft}/review', [WorkspaceContentController::class, 'reviewDraft'])
        ->name('workspaces.content.drafts.review');

    Route::post('workspaces/{workspace}/content/drafts/{draft}/publish', [WorkspaceContentController::class, 'publishDraft'])
        ->name('workspaces.content.drafts.publish');
});
