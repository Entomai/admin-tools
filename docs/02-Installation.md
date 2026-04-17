# Installation

Admin Tools is a normal Botble plugin.

## Requirements

- Botble CMS `7.6.6` or higher.
- PHP `8.2` or higher.

## Install

Copy the plugin folder into:

```bash
platform/plugins/admin-tools
```

Activate it from the Botble admin panel or run:

```bash
php artisan cms:plugin:activate admin-tools
```

## Assets

The plugin loads its admin CSS and JavaScript through Botble assets:

- `vendor/core/plugins/admin-tools/css/fast-menu.css`
- `vendor/core/plugins/admin-tools/js/fast-menu.js`

If you change plugin assets manually, publish or copy them to the public vendor path used by Botble.

## Cache

After installation or updates, clear cached views and optimized bootstrap files:

```bash
php artisan view:clear
php artisan optimize:clear
```

## Deactivation

Admin Tools does not create its own runtime data tables. Removing the plugin should not affect Ecommerce, Contact, Blog, Payment, or other Botble plugin data.
