# Admin Tools

Admin Tools adds a structured left-side header zone to the Botble admin panel. It keeps frequently used admin links, plugin menus, and operational notifications in one predictable place beside the logo.

## What it provides

- A default Fast menu for common admin routes.
- Header menus that share the same design and submenu behavior as the Fast menu.
- Compact notifications with icon, counter, color, dropdown preview, and empty state.
- A flexible custom header-left hook for fully custom HTML.
- A compact header update widget for selectable plugin and theme updates.
- An Admin Tools Settings screen for enabling or disabling header behavior.
- Built-in integrations for Ecommerce, Contact, Blog, and Payments when those plugins are active.

## Header order

Admin Tools keeps the header visually organized:

1. Fast menu.
2. Menus registered with `ADMIN_TOOLS_FILTER_HEADER_MENUS`.
3. Notifications registered with `ADMIN_TOOLS_FILTER_HEADER_NOTIFICATIONS`.
4. The Admin Tools update widget, when updates or provider messages exist.
5. Custom content registered with `ADMIN_TOOLS_FILTER_HEADER_LEFT_ITEMS`.

The order inside each group is controlled by `priority`.

## Extension model

Admin Tools exposes structured filters so plugins do not need to edit Botble core layouts or inject unrelated markup into `BASE_FILTER_TOP_HEADER_LAYOUT`.

Use:

- `ADMIN_TOOLS_FILTER_FAST_MENU_ITEMS` to add links to the default Fast menu.
- `ADMIN_TOOLS_FILTER_HEADER_MENUS` to declare a new header menu.
- `ADMIN_TOOLS_FILTER_HEADER_MENU_ITEMS` to add links to an existing header menu by ID.
- `ADMIN_TOOLS_FILTER_HEADER_NOTIFICATIONS` to add standard notifications.
- `ADMIN_TOOLS_FILTER_NOTIFICATION_SETTING_DEFINITIONS` to add notification settings to the Admin Tools settings page.
- `ADMIN_TOOLS_FILTER_HEADER_LEFT_ITEMS` only when custom HTML is required.
- `ADMIN_TOOLS_FILTER_UPDATE_ITEMS` to add custom update rows to the header update widget.
- `ADMIN_TOOLS_FILTER_INSTALL_UPDATE_ITEM` to handle installation for custom update rows.

## Admin Tools Settings

The settings screen is for Admin Tools behavior, not for managing update lists. It controls the Fast menu, Ecommerce menu, built-in notification groups, the header update widget, sticky admin layout, compact logo width, and the default View website button.

## URL rule

Do not hardcode `/admin`. Always use route names or `route()` so Admin Tools respects the configured Botble admin prefix.
