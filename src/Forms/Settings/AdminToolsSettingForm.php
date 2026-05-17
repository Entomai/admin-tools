<?php

namespace Botble\AdminTools\Forms\Settings;

use Botble\AdminTools\Http\Requests\Settings\AdminToolsSettingRequest;
use Botble\Base\Forms\FieldOptions\MultiChecklistFieldOption;
use Botble\Base\Forms\FieldOptions\OnOffFieldOption;
use Botble\Base\Forms\FieldOptions\TextFieldOption;
use Botble\Base\Forms\Fields\MultiCheckListField;
use Botble\Base\Forms\Fields\OnOffCheckboxField;
use Botble\Base\Forms\Fields\TextField;
use Botble\Setting\Forms\SettingForm;

class AdminToolsSettingForm extends SettingForm
{
    public function setup(): void
    {
        parent::setup();

        $this
            ->template('plugins/admin-tools::settings.form')
            ->setUrl(route('admin-tools.settings.update'))
            ->setSectionTitle(trans('plugins/admin-tools::admin-tools.settings_title'))
            ->setSectionDescription(trans('plugins/admin-tools::admin-tools.settings_description'))
            ->setValidatorClass(AdminToolsSettingRequest::class)
            ->addSwitch('fast_menu_enabled')
            ->addSwitch('fast_cache_cleaner_enabled')
            ->when($this->isPluginActive('ecommerce'), function (self $form): void {
                $form
                    ->addSwitch('ecommerce_header_menu_enabled')
                    ->addSwitch('ecommerce_notifications_enabled')
                    ->addStatusChecklist(
                        'ecommerce_notification_statuses',
                        'Botble\Ecommerce\Enums\OrderStatusEnum',
                        ['pending']
                    );
            })
            ->when($this->isPluginActive('contact'), function (self $form): void {
                $form
                    ->addSwitch('contact_notifications_enabled')
                    ->addStatusChecklist(
                        'contact_notification_statuses',
                        'Botble\Contact\Enums\ContactStatusEnum',
                        ['unread']
                    );
            })
            ->when($this->isPluginActive('payment'), function (self $form): void {
                $form
                    ->addSwitch('payment_notifications_enabled')
                    ->addStatusChecklist(
                        'payment_notification_statuses',
                        'Botble\Payment\Enums\PaymentStatusEnum',
                        ['pending']
                    );
            })
            ->when($this->isPluginActive('hotel'), function (self $form): void {
                $form->addStatusChecklist(
                    'hotel_booking_notification_statuses',
                    'Botble\Hotel\Enums\BookingStatusEnum',
                    ['pending']
                );
            })
            ->addSwitch('update_header_widget_enabled')
            ->addSwitch('sticky_header_enabled')
            ->addSwitch('compact_brand_enabled')
            ->addSwitch('remove_logo_enabled', false)
            ->add(
                'admin_tools_brand_custom_text',
                TextField::class,
                TextFieldOption::make()
                    ->label(trans('plugins/admin-tools::admin-tools.settings_brand_custom_text'))
                    ->value(admin_tools_brand_custom_text())
                    ->maxLength(120)
                    ->helperText(trans('plugins/admin-tools::admin-tools.settings_brand_custom_text_help'))
            )
            ->addSwitch('hide_view_website_button', false)
            ->addSwitch('hide_global_search', false)
            ->addSwitch('hide_botble_notification', false)
            ->addSwitch('admin_appearance_per_user_enabled', false)
            ->add(
                'admin_tools_hidden_header_hook_items[]',
                MultiCheckListField::class,
                MultiChecklistFieldOption::make()
                    ->label(trans('plugins/admin-tools::admin-tools.settings_hidden_header_hook_items'))
                    ->choices(admin_tools_header_hook_item_choices())
                    ->selected(old('admin_tools_hidden_header_hook_items', admin_tools_setting_array('hidden_header_hook_items')))
                    ->helperText(trans('plugins/admin-tools::admin-tools.settings_hidden_header_hook_items_help'))
            );
    }

    protected function addStatusChecklist(string $key, string $enumClass, array $default): static
    {
        return $this->add(
            'admin_tools_'.$key.'[]',
            MultiCheckListField::class,
            MultiChecklistFieldOption::make()
                ->label(trans("plugins/admin-tools::admin-tools.settings_$key"))
                ->choices($this->statusChoices($enumClass, $default))
                ->selected(old('admin_tools_'.$key, admin_tools_setting_array($key, $default)))
                ->helperText(trans("plugins/admin-tools::admin-tools.settings_{$key}_help"))
                ->inline()
        );
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

    protected function statusChoices(string $enumClass, array $fallback): array
    {
        if (class_exists($enumClass) && method_exists($enumClass, 'labels')) {
            return $enumClass::labels();
        }

        return array_combine($fallback, array_map('ucfirst', $fallback));
    }

    protected function isPluginActive(string $plugin): bool
    {
        return function_exists('is_plugin_active') && is_plugin_active($plugin);
    }
}
