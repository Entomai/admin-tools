<?php

namespace Botble\AdminTools\Providers;

use Botble\AdminTools\Package\PackageServiceProvider as EntomaiPackageServiceProvider;
use Botble\AdminTools\Supports\AdminAppearance as AdminToolsAdminAppearance;
use Botble\Base\Facades\PanelSectionManager;
use Botble\Base\PanelSections\PanelSectionItem;
use Botble\Base\Supports\AdminAppearance as BaseAdminAppearance;
use Botble\Base\Supports\ServiceProvider;
use Botble\Base\Traits\LoadAndPublishDataTrait;
use Botble\Setting\PanelSections\SettingOthersPanelSection;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Facade;

class AdminToolsServiceProvider extends ServiceProvider
{
    use LoadAndPublishDataTrait;

    public function register(): void
    {
        $this->app->singleton(BaseAdminAppearance::class, AdminToolsAdminAppearance::class);

        Facade::clearResolvedInstance(BaseAdminAppearance::class);
    }

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

        EntomaiPackageServiceProvider::loadForPlugin('admin-tools');
        $this->app->register(EntomaiPackageServiceProvider::class);

        $this->app->register(HookServiceProvider::class);

        $this->registerEntomaiPluginsMenuVisibility();

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

    protected function registerEntomaiPluginsMenuVisibility(): void
    {
        PanelSectionManager::default()->beforeRendering(function (): void {
            if (! admin_tools_setting_bool('entomai_plugins_menu_enabled', true)) {
                PanelSectionManager::ignoreItemId('entomai-plugins');
            }
        }, PHP_INT_MAX);

        add_action('rendered_dashboard_menu', [$this, 'removeEntomaiPluginsDashboardMenuItem'], PHP_INT_MAX, 2);
    }

    public function removeEntomaiPluginsDashboardMenuItem(mixed $menu, Collection $items): void
    {
        if (admin_tools_setting_bool('entomai_plugins_menu_enabled', true)) {
            return;
        }

        $this->removeDashboardMenuItem($items, 'cms-entomai-plugins');
    }

    protected function removeDashboardMenuItem(Collection $items, string $id): void
    {
        $items->forget($id);

        foreach ($items as $item) {
            $children = $item['children'] ?? null;

            if ($children instanceof Collection) {
                $this->removeDashboardMenuItem($children, $id);
            }
        }
    }
}
