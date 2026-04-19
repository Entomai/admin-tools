<?php

namespace Botble\AdminTools\Providers;

use Botble\Base\Facades\PanelSectionManager;
use Botble\Base\PanelSections\PanelSectionItem;
use Botble\Base\Supports\ServiceProvider;
use Botble\Base\Traits\LoadAndPublishDataTrait;
use Botble\Setting\PanelSections\SettingOthersPanelSection;

class AdminToolsServiceProvider extends ServiceProvider
{
    use LoadAndPublishDataTrait;

    public function boot(): void
    {
        $this
            ->setNamespace('plugins/admin-tools')
            ->loadHelpers()
            ->loadAndPublishConfigurations(['general'])
            ->loadAndPublishConfigurations(['permissions'])
            ->loadRoutes()
            ->loadAndPublishViews()
            ->loadAndPublishTranslations()
            ->publishAssets();

        $this->app->register(HookServiceProvider::class);

        PanelSectionManager::default()->beforeRendering(function (): void {
            PanelSectionManager::registerItem(
                SettingOthersPanelSection::class,
                fn () => PanelSectionItem::make('admin-tools')
                    ->setTitle(trans('plugins/admin-tools::admin-tools.settings_title'))
                    ->withIcon('ti ti-bolt')
                    ->withPriority(140)
                    ->withDescription(trans('plugins/admin-tools::admin-tools.settings_description'))
                    ->withPermission('admin-tools.settings')
                    ->withRoute('admin-tools.settings')
            );
        });
    }
}
