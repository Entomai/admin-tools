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
            ->addCustomNotificationSettings()
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

    protected function addCustomNotificationSettings(): static
    {
        $fieldNames = [];

        foreach (admin_tools_active_notification_setting_definitions() as $definition) {
            $enabledKey = $definition['enabled_key'] ?? '';
            $statusKey = $definition['status_key'] ?? '';
            $statusChoices = $definition['status_choices'] ?? [];

            if ($enabledKey && ! $this->has("admin_tools_$enabledKey")) {
                $this->addSwitchField(
                    $enabledKey,
                    $definition['enabled_label'],
                    $definition['enabled_help'],
                    $definition['enabled_default']
                );

                $fieldNames[] = "admin_tools_$enabledKey";
            }

            if ($statusKey && $statusChoices && ! $this->has("admin_tools_{$statusKey}[]")) {
                $this->addStatusChecklistFromChoices(
                    $statusKey,
                    $statusChoices,
                    $definition['status_default'],
                    $definition['status_label'],
                    $definition['status_help']
                );

                $fieldNames[] = "admin_tools_{$statusKey}[]";
            }
        }

        $this->setFormOption('admin_tools_custom_notification_setting_fields', $fieldNames);

        return $this;
    }

    protected function addStatusChecklist(string $key, string $enumClass, array $default): static
    {
        return $this->addStatusChecklistFromChoices(
            $key,
            $this->statusChoices($enumClass, $default),
            $default,
            trans("plugins/admin-tools::admin-tools.settings_$key"),
            trans("plugins/admin-tools::admin-tools.settings_{$key}_help")
        );
    }

    protected function addSwitch(string $key, bool $default = true): static
    {
        return $this->addSwitchField(
            $key,
            trans("plugins/admin-tools::admin-tools.settings_$key"),
            trans("plugins/admin-tools::admin-tools.settings_{$key}_help"),
            $default
        );
    }

    protected function addSwitchField(string $key, string $label, ?string $helperText, bool $default = true): static
    {
        return $this->add(
            'admin_tools_'.$key,
            OnOffCheckboxField::class,
            tap(
                OnOffFieldOption::make()
                    ->defaultValue(admin_tools_setting_bool($key, $default))
                    ->label($label),
                fn (OnOffFieldOption $option) => $helperText ? $option->helperText($helperText) : null
            )
        );
    }

    protected function addStatusChecklistFromChoices(
        string $key,
        array $choices,
        array $default,
        string $label,
        ?string $helperText
    ): static {
        return $this->add(
            'admin_tools_'.$key.'[]',
            MultiCheckListField::class,
            tap(
                MultiChecklistFieldOption::make()
                    ->label($label)
                    ->choices($choices)
                    ->selected(old('admin_tools_'.$key, admin_tools_setting_array($key, $default)))
                    ->inline(),
                fn (MultiChecklistFieldOption $option) => $helperText ? $option->helperText($helperText) : null
            )
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
