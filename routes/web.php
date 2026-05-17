<?php

use Botble\AdminTools\Http\Controllers\AdminAppearancePreferenceController;
use Botble\AdminTools\Http\Controllers\AdminToolsCacheController;
use Botble\AdminTools\Http\Controllers\Settings\AdminToolsSettingController;
use Botble\AdminTools\Package\Http\Controllers\PrivateUpdaterController as EntomaiPrivatePluginUpdateController;
use Botble\Base\Facades\AdminHelper;
use Illuminate\Support\Facades\Route;

AdminHelper::registerRoutes(function (): void {
    Route::group(['prefix' => 'admin-tools', 'as' => 'admin-tools.'], function (): void {
        Route::group(['permission' => 'admin-tools.settings'], function (): void {
            Route::get('settings', [AdminToolsSettingController::class, 'edit'])
                ->name('settings');

            Route::put('settings', [AdminToolsSettingController::class, 'update'])
                ->name('settings.update')
                ->middleware('preventDemo');

            Route::post('header-updates', [AdminToolsSettingController::class, 'updateSelected'])
                ->name('updates.update')
                ->middleware('preventDemo');
        });

        Route::post('cache/clear', [AdminToolsCacheController::class, 'clear'])
            ->name('cache.clear')
            ->permission('superuser')
            ->middleware('preventDemo');

        Route::get('admin-area-settings', [AdminAppearancePreferenceController::class, 'edit'])
            ->name('admin-appearance.edit');

        Route::put('admin-area-settings', [AdminAppearancePreferenceController::class, 'update'])
            ->name('admin-appearance.update')
            ->middleware('preventDemo');
    });

    if (! Route::has('entomai.private-updater.check')) {
        Route::group([
            'prefix' => 'entomai/private-plugin-updater',
            'as' => 'entomai.private-updater.',
            'permission' => 'plugins.index',
        ], function (): void {
            Route::post('check', [EntomaiPrivatePluginUpdateController::class, 'check'])
                ->name('check');

            Route::post('{plugin}/update', [EntomaiPrivatePluginUpdateController::class, 'update'])
                ->name('update')
                ->middleware('preventDemo');
        });
    }
});
