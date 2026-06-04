<?php

use Illuminate\Support\Facades\Route;
use Modules\Workspaces\Http\Controllers\InvitationAcceptController;
use Modules\Workspaces\Http\Controllers\TeamController;
use Modules\Workspaces\Http\Controllers\WorkspacesController;

Route::middleware(['auth', 'verified'])->group(function () {
    Route::resource('workspaces', WorkspacesController::class)->only([
        'index',
        'create',
        'store',
        'show',
    ]);

    Route::post('workspaces/{workspace}/members', [WorkspacesController::class, 'assignMember'])
        ->name('workspaces.members.store');

    Route::get('team', [TeamController::class, 'index'])->name('team.index');
    Route::post('team/invitations', [TeamController::class, 'storeInvitation'])
        ->name('team.invitations.store');
    Route::delete('team/invitations/{invitation}', [TeamController::class, 'destroyInvitation'])
        ->name('team.invitations.destroy');
});

Route::middleware('guest')->group(function () {
    Route::get('invitations/{token}', [InvitationAcceptController::class, 'show'])
        ->name('invitations.show');
    Route::post('invitations/{token}', [InvitationAcceptController::class, 'store'])
        ->name('invitations.store');
});
