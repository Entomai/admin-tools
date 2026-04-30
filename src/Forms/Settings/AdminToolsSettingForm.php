<?php

namespace Botble\AdminTools\Forms\Settings;

use Botble\AdminTools\Http\Requests\Settings\AdminToolsSettingRequest;
use Botble\Base\Forms\FieldOptions\OnOffFieldOption;
use Botble\Base\Forms\Fields\OnOffCheckboxField;
use Botble\Setting\Forms\SettingForm;

class AdminToolsSettingForm extends SettingForm
{
    public function setup(): void
    {
        parent::setup();

        $this
            ->setUrl(route('admin-tools.settings.update'))
            ->setSectionTitle(trans('plugins/admin-tools::admin-tools.settings_title'))
            ->setSectionDescription(trans('plugins/admin-tools::admin-tools.settings_description'))
            ->setValidatorClass(AdminToolsSettingRequest::class)
            ->addSwitch('fast_menu_enabled')
            ->addSwitch('fast_cache_cleaner_enabled')
            ->addSwitch('ecommerce_header_menu_enabled')
            ->addSwitch('ecommerce_notifications_enabled')
            ->addSwitch('contact_notifications_enabled')
            ->addSwitch('payment_notifications_enabled')
            ->addSwitch('update_header_widget_enabled')
            ->addSwitch('sticky_header_enabled')
            ->addSwitch('compact_brand_enabled')
            ->addSwitch('hide_view_website_button', false);
    }

    protected function addSwitch(string $key, bool $default = true): static
    {
        return $this->add(
            'admin_tools_'.$key,
            OnOffCheckboxField::class,
            OnOffFieldOption::make()
                ->defaultValue(admin_tools_setting_bool($key, $default))
                ->label(trans("plugins/admin-tools::admin-tools.settings_$key"))
                ->helperText(trans("plugins/admin-tools::admin-tools.settings_{$key}_help"))
        );
    }
}
