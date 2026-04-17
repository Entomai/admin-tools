# Permissions and safety

Admin Tools is designed to fail closed. If a route, class, plugin, or permission is missing, the related item should not render.

## Permission checks

Use `permission` or `permissions` on menu items, notifications, and custom items.

```php
[
    'label' => 'Products',
    'route' => 'products.index',
    'permission' => 'products.index',
]
```

Admin Tools checks permissions through the current authenticated admin user.

## Route checks

When using `route`, Admin Tools checks `Route::has()` before generating the URL.

If the route is missing, the item is removed unless it still has visible children.

## Plugin checks

When integrating with optional plugins, guard your code:

```php
if (! function_exists('is_plugin_active') || ! is_plugin_active('ecommerce')) {
    return $items;
}
```

For model integrations, also check `class_exists()`.

## Avoid unsafe eager loading

Optional plugins can leave polymorphic relations pointing to classes that are not currently loaded.

For header notifications, query only the fields you need. Avoid eager loading optional morph relations unless every target class is guaranteed to exist.

## Keep the header lightweight

The admin header renders on many pages. Keep queries small:

- use `count()` for badges
- limit dropdown items
- select only needed columns when possible
- avoid expensive aggregates
- catch optional integration failures

## Do not edit core layouts

Admin Tools already uses `BASE_FILTER_TOP_HEADER_LAYOUT` internally. Other plugins should extend Admin Tools hooks instead of editing Botble layout files or injecting competing header blocks.
