<?php

use Botble\AdminTools\Http\Controllers\Settings\AdminToolsSettingController;
use Botble\Base\Facades\AdminHelper;
use Illuminate\Support\Facades\Route;

AdminHelper::registerRoutes(function (): void {
    Route::group([
        'prefix' => 'admin-tools',
        'as' => 'admin-tools.',
        'permission' => 'admin-tools.settings',
    ], function (): void {
        Route::get('settings', [AdminToolsSettingController::class, 'edit'])
            ->name('settings');

        Route::put('settings', [AdminToolsSettingController::class, 'update'])
            ->name('settings.update')
            ->middleware('preventDemo');

        Route::post('header-updates', [AdminToolsSettingController::class, 'updateSelected'])
            ->name('updates.update')
            ->middleware('preventDemo');
    });
});
