<?php

namespace Botble\AdminTools\Supports;

use Botble\Base\Supports\AdminAppearance as BaseAdminAppearance;

class AdminAppearance extends BaseAdminAppearance
{
    public function getLocale(): string
    {
        return (string) $this->customSetting('locale', parent::getLocale());
    }

    public function getLocaleDirection(): string
    {
        return (string) $this->customSetting('locale_direction', parent::getLocaleDirection());
    }

    public function getCurrentLayout(): string
    {
        return (string) $this->customSetting('layout', parent::getCurrentLayout());
    }

    public function getContainerWidth(): string
    {
        return (string) $this->customSetting('container_width', parent::getContainerWidth());
    }

    public function showMenuItemIcon(): bool
    {
        return filter_var($this->customSetting('show_menu_item_icon', parent::showMenuItemIcon()), FILTER_VALIDATE_BOOL);
    }

    protected function customSetting(string $key, mixed $default = null): mixed
    {
        if (! $this->shouldUseCustomPreferences() || ! function_exists('admin_tools_current_admin_appearance_setting')) {
            return $default;
        }

        return admin_tools_current_admin_appearance_setting($key, $default);
    }

    protected function shouldUseCustomPreferences(): bool
    {
        if (! auth()->guard()->check()) {
            return false;
        }

        return ! request()->routeIs('settings.admin-appearance*');
    }
}
