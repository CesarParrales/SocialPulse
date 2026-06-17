<?php

use Illuminate\Support\Facades\Route;
use Modules\Notifications\Http\Controllers\NotificationsController;

Route::middleware(['auth', 'verified'])->group(function () {
    Route::get('notifications', [NotificationsController::class, 'index'])
        ->name('notifications.index');
    Route::patch('notifications/{notification}/read', [NotificationsController::class, 'markRead'])
        ->name('notifications.read');
    Route::post('notifications/read-all', [NotificationsController::class, 'markAllRead'])
        ->name('notifications.read-all');
});
