<?php

use Botble\Base\Facades\BaseHelper;
use Botble\Base\Facades\Html;

if (! defined('ADMIN_TOOLS_FILTER_FAST_MENU_ITEMS')) {
    define('ADMIN_TOOLS_FILTER_FAST_MENU_ITEMS', 'admin_tools_filter_fast_menu_items');
}

if (! defined('ADMIN_TOOLS_FILTER_HEADER_LEFT_ITEMS')) {
    define('ADMIN_TOOLS_FILTER_HEADER_LEFT_ITEMS', 'admin_tools_filter_header_left_items');
}

if (! defined('ADMIN_TOOLS_FILTER_HEADER_NOTIFICATIONS')) {
    define('ADMIN_TOOLS_FILTER_HEADER_NOTIFICATIONS', 'admin_tools_filter_header_notifications');
}

if (! defined('ADMIN_TOOLS_FILTER_HEADER_MENUS')) {
    define('ADMIN_TOOLS_FILTER_HEADER_MENUS', 'admin_tools_filter_header_menus');
}

if (! defined('ADMIN_TOOLS_FILTER_HEADER_MENU_ITEMS')) {
    define('ADMIN_TOOLS_FILTER_HEADER_MENU_ITEMS', 'admin_tools_filter_header_menu_items');
}

if (! defined('ADMIN_TOOLS_FILTER_UPDATE_ITEMS')) {
    define('ADMIN_TOOLS_FILTER_UPDATE_ITEMS', 'admin_tools_filter_update_items');
}

if (! defined('ADMIN_TOOLS_FILTER_INSTALL_UPDATE_ITEM')) {
    define('ADMIN_TOOLS_FILTER_INSTALL_UPDATE_ITEM', 'admin_tools_filter_install_update_item');
}

if (! defined('ADMIN_TOOLS_FILTER_NOTIFICATION_SETTING_DEFINITIONS')) {
    define('ADMIN_TOOLS_FILTER_NOTIFICATION_SETTING_DEFINITIONS', 'admin_tools_filter_notification_setting_definitions');
}

if (! function_exists('admin_tools_setting')) {
    function admin_tools_setting(string $key, mixed $default = null): mixed
    {
        return setting('admin_tools_'.$key, config("plugins.admin-tools.general.$key", $default));
    }
}

if (! function_exists('admin_tools_setting_bool')) {
    function admin_tools_setting_bool(string $key, bool $default = false): bool
    {
        return filter_var(admin_tools_setting($key, $default), FILTER_VALIDATE_BOOL);
    }
}

if (! function_exists('admin_tools_setting_array')) {
    function admin_tools_setting_array(string $key, array $default = []): array
    {
        $value = admin_tools_setting($key, $default);

        if (is_array($value)) {
            return $value;
        }

        if (! is_string($value) || $value === '') {
            return $default;
        }

        $decoded = json_decode($value, true);

        return is_array($decoded) ? $decoded : $default;
    }
}

if (! function_exists('admin_tools_notification_setting_definitions')) {
    function admin_tools_notification_setting_definitions(): array
    {
        $definitions = apply_filters(ADMIN_TOOLS_FILTER_NOTIFICATION_SETTING_DEFINITIONS, []);

        if (! is_array($definitions)) {
            return [];
        }

        $normalized = [];

        foreach ($definitions as $id => $definition) {
            if (! is_array($definition)) {
                continue;
            }

            $id = is_string($id) ? $id : ($definition['id'] ?? null);

            if (! is_string($id) || trim($id) === '') {
                continue;
            }

            $baseKey = admin_tools_normalize_notification_setting_key($id);

            if ($baseKey === '') {
                continue;
            }

            $plugin = $definition['plugin'] ?? null;
            $plugin = is_string($plugin) && $plugin !== '' ? $plugin : null;

            $enabledKey = admin_tools_normalize_notification_setting_key(
                $definition['enabled_key'] ?? $definition['enable_key'] ?? "{$baseKey}_notifications_enabled"
            );
            $statusKey = admin_tools_normalize_notification_setting_key(
                $definition['status_key'] ?? $definition['statuses_key'] ?? "{$baseKey}_notification_statuses"
            );
            $statusChoices = admin_tools_normalize_notification_status_choices(
                $definition['status_choices'] ?? $definition['choices'] ?? []
            );
            $statusDefault = admin_tools_normalize_notification_status_default(
                $definition['status_default'] ?? $definition['statuses_default'] ?? array_keys($statusChoices)
            );

            $enabledDefault = filter_var(
                $definition['enabled_default'] ?? true,
                FILTER_VALIDATE_BOOL,
                FILTER_NULL_ON_FAILURE
            );

            $normalized[$id] = [
                'id' => $id,
                'plugin' => $plugin,
                'enabled_key' => $enabledKey,
                'enabled_label' => admin_tools_notification_setting_text(
                    $definition['enabled_label'] ?? null,
                    admin_tools_label_from_setting_key($enabledKey)
                ),
                'enabled_help' => admin_tools_notification_setting_text(
                    $definition['enabled_help'] ?? $definition['enabled_helper_text'] ?? null
                ),
                'enabled_default' => $enabledDefault ?? true,
                'status_key' => $statusKey,
                'status_label' => admin_tools_notification_setting_text(
                    $definition['status_label'] ?? $definition['statuses_label'] ?? null,
                    admin_tools_label_from_setting_key($statusKey)
                ),
                'status_help' => admin_tools_notification_setting_text(
                    $definition['status_help'] ?? $definition['statuses_help'] ?? $definition['status_helper_text'] ?? null
                ),
                'status_choices' => $statusChoices,
                'status_default' => $statusDefault,
            ];
        }

        return $normalized;
    }
}

if (! function_exists('admin_tools_active_notification_setting_definitions')) {
    function admin_tools_active_notification_setting_definitions(): array
    {
        return array_filter(
            admin_tools_notification_setting_definitions(),
            function (array $definition): bool {
                $plugin = $definition['plugin'] ?? null;

                return ! $plugin || (function_exists('is_plugin_active') && is_plugin_active($plugin));
            }
        );
    }
}

if (! function_exists('admin_tools_normalize_notification_setting_key')) {
    function admin_tools_normalize_notification_setting_key(mixed $key): string
    {
        if (! is_scalar($key)) {
            return '';
        }

        $key = strtolower(trim((string) $key));

        if (str_starts_with($key, 'admin_tools_')) {
            $key = substr($key, strlen('admin_tools_'));
        }

        return trim((string) preg_replace('/[^a-z0-9_]+/', '_', $key), '_');
    }
}

if (! function_exists('admin_tools_normalize_notification_status_choices')) {
    function admin_tools_normalize_notification_status_choices(mixed $choices): array
    {
        if (! is_array($choices)) {
            return [];
        }

        $normalized = [];

        foreach ($choices as $value => $label) {
            if (is_int($value)) {
                if (! is_scalar($label)) {
                    continue;
                }

                $value = (string) $label;
            }

            if (! is_scalar($value)) {
                continue;
            }

            $value = trim((string) $value);

            if ($value === '') {
                continue;
            }

            $normalized[$value] = admin_tools_notification_setting_text(
                $label,
                admin_tools_label_from_setting_key($value)
            );
        }

        return $normalized;
    }
}

if (! function_exists('admin_tools_normalize_notification_status_default')) {
    function admin_tools_normalize_notification_status_default(mixed $default): array
    {
        if (! is_array($default)) {
            return [];
        }

        return array_values(array_filter(array_map(
            fn (mixed $value): ?string => is_scalar($value) ? trim((string) $value) : null,
            $default
        ), fn (?string $value): bool => $value !== null && $value !== ''));
    }
}

if (! function_exists('admin_tools_notification_setting_text')) {
    function admin_tools_notification_setting_text(mixed $value, ?string $default = null): ?string
    {
        if (! is_scalar($value)) {
            return $default;
        }

        $value = trim((string) $value);

        return $value === '' ? $default : $value;
    }
}

if (! function_exists('admin_tools_label_from_setting_key')) {
    function admin_tools_label_from_setting_key(string $key): string
    {
        return ucwords(str_replace('_', ' ', $key));
    }
}

if (! function_exists('admin_tools_brand_custom_text')) {
    function admin_tools_brand_custom_text(): string
    {
        $value = admin_tools_setting('brand_custom_text', '');

        return is_scalar($value) ? trim((string) $value) : '';
    }
}

if (! function_exists('admin_tools_should_show_brand_logo')) {
    function admin_tools_should_show_brand_logo(): bool
    {
        return ! admin_tools_setting_bool('remove_logo_enabled', false)
            && (bool) (setting('admin_logo') || config('core.base.general.logo'));
    }
}

if (! function_exists('admin_tools_has_admin_brand')) {
    function admin_tools_has_admin_brand(): bool
    {
        return admin_tools_should_show_brand_logo() || admin_tools_brand_custom_text() !== '';
    }
}

if (! function_exists('admin_tools_admin_appearance_fields')) {
    function admin_tools_admin_appearance_fields(): array
    {
        return [
            'locale',
            'locale_direction',
            'layout',
            'container_width',
            'show_menu_item_icon',
            'theme_mode',
            'primary_color',
            'secondary_color',
            'heading_color',
            'text_color',
            'link_color',
            'link_hover_color',
        ];
    }
}

if (! function_exists('admin_tools_admin_appearance_meta_key')) {
    function admin_tools_admin_appearance_meta_key(string $key): string
    {
        return 'admin_tools_admin_appearance_'.$key;
    }
}

if (! function_exists('admin_tools_admin_appearance_global_default')) {
    function admin_tools_admin_appearance_global_default(string $key, mixed $default = null): mixed
    {
        return match ($key) {
            'locale' => setting('admin_appearance_locale', config('core.base.general.locale', config('app.locale'))),
            'locale_direction' => setting('admin_appearance_locale_direction', setting('admin_locale_direction', 'ltr')),
            'layout' => setting('admin_appearance_layout', 'vertical'),
            'container_width' => setting('admin_appearance_container_width', 'container-xl'),
            'show_menu_item_icon' => setting('admin_appearance_show_menu_item_icon', true),
            'theme_mode' => auth()->guard()->user()?->getMeta('theme_mode', 'light') ?: 'light',
            'primary_color' => setting('admin_primary_color', '#206bc4'),
            'secondary_color' => setting('admin_secondary_color', '#6c7a91'),
            'heading_color' => setting('admin_heading_color', 'inherit'),
            'text_color' => setting('admin_text_color', '#182433'),
            'link_color' => setting('admin_link_color', '#206bc4'),
            'link_hover_color' => setting('admin_link_hover_color', '#1a569d'),
            default => $default,
        };
    }
}

if (! function_exists('admin_tools_user_admin_appearance_value')) {
    function admin_tools_user_admin_appearance_value(string $key, mixed $default = null, $user = null): mixed
    {
        $user ??= auth()->guard()->user();

        if (! $user || ! method_exists($user, 'getMeta')) {
            return $default;
        }

        $metaKey = $key === 'theme_mode' ? 'theme_mode' : admin_tools_admin_appearance_meta_key($key);
        $value = $user->getMeta($metaKey);

        return $value === null || $value === '' ? $default : $value;
    }
}

if (! function_exists('admin_tools_current_user_admin_appearance_setting')) {
    function admin_tools_current_user_admin_appearance_setting(string $key, mixed $default = null): mixed
    {
        if (! admin_tools_setting_bool('admin_appearance_per_user_enabled', false)) {
            return $default;
        }

        return admin_tools_user_admin_appearance_value($key, $default);
    }
}

if (! function_exists('admin_tools_current_admin_appearance_setting')) {
    function admin_tools_current_admin_appearance_setting(string $key, mixed $default = null): mixed
    {
        $value = admin_tools_current_user_admin_appearance_setting($key);

        if ($value !== null && $value !== '') {
            return $value;
        }

        if (function_exists('hotel_pro_current_admin_appearance_setting')) {
            return hotel_pro_current_admin_appearance_setting($key, $default, false);
        }

        return $default;
    }
}

if (! function_exists('admin_tools_admin_appearance_form_value')) {
    function admin_tools_admin_appearance_form_value(string $key, mixed $default = null, $user = null): mixed
    {
        return admin_tools_user_admin_appearance_value(
            $key,
            admin_tools_admin_appearance_global_default($key, $default),
            $user
        );
    }
}

if (! function_exists('admin_tools_admin_appearance_is_hex_color')) {
    function admin_tools_admin_appearance_is_hex_color(mixed $value): bool
    {
        return is_string($value) && preg_match('/^#(?:[0-9a-fA-F]{3}){1,2}$/', $value) === 1;
    }
}

if (! function_exists('admin_tools_render_admin_appearance_head')) {
    function admin_tools_render_admin_appearance_head(?string $html = null): string
    {
        if (! auth()->guard()->check()) {
            return $html ?: '';
        }

        $variables = [];
        $rgbVariables = [
            'primary_color' => '--primary-color-rgb',
            'secondary_color' => '--secondary-color-rgb',
            'text_color' => '--text-color-rgb',
            'link_color' => '--link-color-rgb',
            'link_hover_color' => '--link-hover-color-rgb',
        ];
        $colorVariables = [
            'primary_color' => '--primary-color',
            'secondary_color' => '--secondary-color',
            'text_color' => '--text-color',
            'link_color' => '--link-color',
            'link_hover_color' => '--link-hover-color',
        ];

        foreach ($colorVariables as $key => $variable) {
            $value = admin_tools_current_admin_appearance_setting($key);

            if (! admin_tools_admin_appearance_is_hex_color($value)) {
                continue;
            }

            $variables[] = sprintf('%s: %s;', $variable, $value);
            $variables[] = sprintf('%s: %s;', $rgbVariables[$key], implode(', ', BaseHelper::hexToRgb($value)));
        }

        $headingColor = admin_tools_current_admin_appearance_setting('heading_color');

        if ($headingColor === 'inherit' || admin_tools_admin_appearance_is_hex_color($headingColor)) {
            $variables[] = sprintf('--heading-color: %s;', $headingColor);
        }

        if ($variables === []) {
            return $html ?: '';
        }

        return ($html ?: '').Html::tag('style', ':root {'."\n".implode("\n", $variables)."\n".'}');
    }
}

if (! function_exists('admin_tools_render_admin_appearance_body_script')) {
    function admin_tools_render_admin_appearance_body_script(?string $html = null): string
    {
        if (! auth()->guard()->check()) {
            return $html ?: '';
        }

        $themeMode = admin_tools_current_admin_appearance_setting('theme_mode');

        if (! in_array($themeMode, ['light', 'dark'], true)) {
            return $html ?: '';
        }

        $script = sprintf(
            "(function(){var mode=%s;document.documentElement.setAttribute('data-bs-theme',mode);if(document.body){if(mode==='dark'){document.body.setAttribute('data-bs-theme','dark')}else{document.body.removeAttribute('data-bs-theme')}}})();",
            json_encode($themeMode)
        );

        return ($html ?: '').Html::tag('script', $script);
    }
}

if (! function_exists('admin_tools_header_hook_item_choices')) {
    function admin_tools_header_hook_item_choices(): array
    {
        $choices = [];
        $hiddenItems = admin_tools_setting_array('hidden_header_hook_items');

        if (
            in_array('hotel-booking-notification', $hiddenItems, true)
            || (function_exists('is_plugin_active') && is_plugin_active('hotel'))
        ) {
            $choices['hotel-booking-notification'] = trans('plugins/admin-tools::admin-tools.header_hook_item_hotel_booking_notification');
        }

        if (
            in_array('contact-notification', $hiddenItems, true)
            || (function_exists('is_plugin_active') && is_plugin_active('contact'))
        ) {
            $choices['contact-notification'] = trans('plugins/admin-tools::admin-tools.header_hook_item_contact_notification');
        }

        if (
            in_array('hotel-pro-booking-notification', $hiddenItems, true)
            || (function_exists('is_plugin_active') && is_plugin_active('hotel-pro'))
        ) {
            $choices['hotel-pro-booking-notification'] = trans('plugins/admin-tools::admin-tools.header_hook_item_hotel_pro_booking_notification');
        }

        foreach (admin_tools_setting_array('header_hook_item_catalog') as $id => $item) {
            if (! is_string($id) || $id === '') {
                continue;
            }

            if (in_array($id, [
                'admin-tools-header-left',
                'botble-admin-notification',
                'admin-tools-contact-notification',
                'admin-tools-ecommerce-notification',
                'admin-tools-payment-notification',
            ], true)) {
                continue;
            }

            $label = is_array($item) ? ($item['label'] ?? null) : $item;

            if (is_string($label) && $label !== '') {
                $choices[$id] = $label;
            }
        }

        foreach ($hiddenItems as $id) {
            if (is_string($id) && $id !== '' && ! isset($choices[$id])) {
                $choices[$id] = $id;
            }
        }

        return $choices;
    }
}
