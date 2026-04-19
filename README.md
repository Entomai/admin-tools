# Admin Tools for Botble CMS

Admin Tools turns the Botble admin header into a faster, cleaner control area for real daily work.

It adds a WordPress-style Fast menu, plugin-ready header menus, compact notifications, a quick update widget, and a dedicated settings screen for controlling what appears in the admin header. No core layout edits, no scattered custom buttons, no fragile one-off dropdowns.

## Highlights

- Fast menu beside the logo for Pages, Posts, Plugins, Themes, Settings, and Admin Config.
- Clean left-side header zone with menus grouped before notifications.
- Optional icon-only Ecommerce menu with Orders, Payments, Products, and Customers.
- Compact notifications with icon, counter, color, dropdown preview, and empty state.
- Header update widget for selecting plugin and theme updates without opening Installed Plugins.
- Sticky admin header with independent sidebar/content scrolling.
- Admin Tools Settings for enabling or disabling Fast menu, integrations, notifications, sticky header, compact brand width, update widget, and the default View website button.
- Extension hooks so other plugins can add Fast menu items, custom header menus, notifications, raw header content, or update providers.

## Built-In Integrations

Admin Tools only loads integrations when the related plugin is active and the required routes/classes exist.

- Ecommerce: icon menu, orders shortcut, products shortcut, customers shortcut, pending order notification.
- Payments: payments shortcut and pending payment notification.
- Contact: unread message notification.
- Blog: posts access in the default Fast menu.

## Extension Points

Other plugins can extend Admin Tools without editing Botble core:

- Add items to the default Fast menu.
- Register a new header menu with the same design and submenu behavior.
- Add items to another plugin's header menu by menu ID.
- Add structured notifications using icon, count, title, description, items, and route/url data.
- Render fully custom header content beside the Fast menu.
- Add custom plugin/theme update providers to the header update widget.

## Documentation

Full documentation:

[entomai.com/docs/admin-tools](https://entomai.com/docs/admin-tools)

Local technical docs:

- [Overview](docs/01-Overview.md)
- [Header System](docs/03-Header-System)
- [Integrations](docs/04-Integrations)
- [Hook Reference](docs/05-Reference/01-Hooks.md)
- [Header Update Widget](docs/06-Header-Update-Widget)
- [Admin Tools Settings](docs/07-Settings/01-Admin-Tools-Settings.md)

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

Clear cached data after activation:

```bash
php artisan optimize:clear
```

## License

MIT
