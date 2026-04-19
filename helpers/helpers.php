<?php

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
