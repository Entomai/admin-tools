# Changelog

All notable changes to Admin Tools will be documented in this file.

## 1.0.0

### Added

- Added a WordPress-style Fast menu in the Botble admin header.
- Added default Fast menu shortcuts for Pages, Posts, Plugins, Themes, Settings, and Admin Config.
- Added submenu support for Fast menu and custom header menus.
- Added a dedicated left-side admin header zone beside the logo.
- Added structured hooks for extending Fast menu items, custom header menus, header menu items, notifications, custom header content, and update providers.
- Added built-in Ecommerce header menu support with Orders, Payments, Products, and Customers.
- Added built-in Ecommerce order notifications.
- Added built-in Contact unread message notifications.
- Added built-in Payment pending transaction notifications.
- Added a compact header update widget for selectable plugin and theme updates.
- Added support for Botble Marketplace plugin updates.
- Added support for Botble Marketplace theme updates when themes declare valid marketplace metadata.
- Added support for Entomai private plugin update providers when available.
- Added Admin Tools Settings for controlling Fast menu, Ecommerce menu, built-in notifications, header update widget, sticky header, compact brand width, and the default View website button.
- Added sticky admin header behavior with independent sidebar and page content scrolling.
- Added compact brand width handling from plugin CSS.
- Added JavaScript header relocation so Admin Tools can render through Botble's top header filter and move itself beside the logo.
- Added technical documentation for the header system, integrations, hooks, header update widget, and settings.

### Changed

- Grouped header menus before notification widgets to keep the header order predictable.
- Kept the built-in Ecommerce menu icon-only by default.
- Moved update controls out of Admin Tools Settings and into the header update widget.

### Fixed

- Prevented Admin Tools integrations from loading when related plugins, classes, routes, or permissions are unavailable.
- Prevented empty menu labels from rendering extra spacing in header menus.
- Avoided duplicate legacy plugin notifications when Admin Tools notifications are active.
