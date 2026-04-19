# Admin Tools Settings

Admin Tools Settings is the control panel for this plugin. It does not manage update rows directly; updates are handled by the header update widget.

Route:

```php
route('admin-tools.settings')
```

Permission:

```php
admin-tools.settings
```

## Available Toggles

- `admin_tools_fast_menu_enabled`: show or hide the default Fast menu.
- `admin_tools_ecommerce_header_menu_enabled`: show or hide the built-in Ecommerce header menu.
- `admin_tools_ecommerce_notifications_enabled`: show or hide the built-in order notification.
- `admin_tools_contact_notifications_enabled`: show or hide the built-in contact notification.
- `admin_tools_payment_notifications_enabled`: show or hide the built-in payment notification.
- `admin_tools_update_header_widget_enabled`: show or hide the header update widget.
- `admin_tools_sticky_header_enabled`: enable or disable the sticky admin header and independent page/sidebar scrolling.
- `admin_tools_compact_brand_enabled`: reduce the logo area when header-left tools are visible.
- `admin_tools_hide_view_website_button`: hide Botble's default View website button from the admin header.

## Reading Settings In Code

Use the helper when adding future Admin Tools behavior:

```php
if (admin_tools_setting_bool('sticky_header_enabled', true)) {
    // Add sticky-header behavior.
}
```

For non-boolean values:

```php
$value = admin_tools_setting('some_future_key', 'default');
```

Settings are stored with the `admin_tools_` prefix and fall back to `config/plugins/admin-tools/general.php`.
