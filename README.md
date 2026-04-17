# Admin Tools for Botble CMS

Admin Tools makes the Botble admin header faster, smarter, and easier to extend.

It adds a WordPress-style fast menu, plugin-ready header menus, and compact notifications that stay visually consistent across the admin panel. Instead of scattering custom buttons, badges, and dropdowns across different plugins, Admin Tools gives your Botble back office one clean place for shortcuts and operational alerts.

## Why It Feels Better

- A fast admin menu beside the logo for common actions.
- Extra header menus with the same design and submenu behavior as the default Fast menu.
- Compact notifications with icons, counters, colors, empty states, and dropdown previews.
- A dedicated left-side header zone that keeps menus together and notifications together.
- Extension hooks so other plugins can add items without editing Botble core or this plugin.

## Built-In Compatibility

Admin Tools detects active plugins and only loads what is available.

- Ecommerce: orders, products, customers, and order notifications.
- Payments: payment shortcuts and pending payment notifications.
- Contact: unread message notifications.
- Blog: default Fast menu access to posts when Blog is active.

## Developer Friendly

Other plugins can extend the header through structured hooks:

- Add items to the default Fast menu.
- Create a new custom header menu.
- Add menu items to an existing custom menu by ID.
- Add standard notifications without writing custom header markup.
- Render fully custom header content when needed.

## Documentation

Learn how to add custom menus, notifications, and menu items to the default Fast menu:

[entomai.com/docs/admin-tools](https://entomai.com/docs/admin-tools)

Local technical reference:

[docs/01-Overview.md](docs/01-Overview.md)

## Requirements

- Botble CMS `7.6.6` or higher.
- PHP `8.2` or higher.

## Installation

Copy the plugin into:

```bash
platform/plugins/admin-tools
```

Then activate it from the Botble admin panel or run:

```bash
php artisan cms:plugin:activate admin-tools
```

## License

MIT
