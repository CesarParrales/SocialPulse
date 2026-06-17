<?php

use Illuminate\Support\Facades\Route;
use Modules\Settings\Http\Controllers\AgencySettingsController;
use Modules\Settings\Http\Controllers\IntegrationCredentialsController;
use Modules\Settings\Http\Controllers\PlatformSettingsController;
use Modules\Settings\Http\Controllers\SettingsIndexController;
use Modules\Settings\Http\Controllers\WorkspaceSettingsController;

Route::middleware(['auth', 'verified'])->group(function () {
    Route::get('settings', SettingsIndexController::class)
        ->name('settings.index');

    Route::put('settings/platform/integrations', [IntegrationCredentialsController::class, 'updatePlatform'])
        ->name('settings.platform.integrations.update');

    Route::post('settings/platform/integrations/import-env', [IntegrationCredentialsController::class, 'importPlatformFromEnv'])
        ->name('settings.platform.integrations.import-env');

    Route::put('settings/agency/integrations', [IntegrationCredentialsController::class, 'updateAgency'])
        ->name('settings.agency.integrations.update');

    Route::put('settings/platform/agencies/{agency}/integrations', [IntegrationCredentialsController::class, 'updateAgencyFromPlatform'])
        ->name('settings.platform.agencies.integrations.update');

    Route::post('settings/platform/agencies/{agency}/integrations/import-env', [IntegrationCredentialsController::class, 'importAgencyFromEnv'])
        ->name('settings.platform.agencies.integrations.import-env');

    Route::get('settings/platform', [PlatformSettingsController::class, 'index'])
        ->name('settings.platform.index');

    Route::get('settings/platform/agencies/create', [PlatformSettingsController::class, 'createAgency'])
        ->name('settings.platform.agencies.create');

    Route::post('settings/platform/agencies', [PlatformSettingsController::class, 'storeAgency'])
        ->name('settings.platform.agencies.store');

    Route::get('settings/platform/agencies/{agency}', [PlatformSettingsController::class, 'editAgency'])
        ->name('settings.platform.agencies.edit');

    Route::put('settings/platform/agencies/{agency}', [PlatformSettingsController::class, 'updateAgency'])
        ->name('settings.platform.agencies.update');

    Route::get('settings/agency', [AgencySettingsController::class, 'edit'])
        ->name('settings.agency.edit');

    Route::put('settings/agency', [AgencySettingsController::class, 'update'])
        ->name('settings.agency.update');

    Route::get('settings/workspaces/{workspace}', [WorkspaceSettingsController::class, 'edit'])
        ->name('settings.workspace.edit');

    Route::put('settings/workspaces/{workspace}', [WorkspaceSettingsController::class, 'update'])
        ->name('settings.workspace.update');
});
