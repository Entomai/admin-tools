# Ecommerce integration

Admin Tools integrates with Botble Ecommerce when the `ecommerce` plugin is active.

The integration is guarded by:

- `is_plugin_active('ecommerce')`
- required classes
- required routes
- current user permissions

If any dependency is missing, Admin Tools skips the Ecommerce feature instead of throwing an error.

## Ecommerce header menu

Admin Tools registers the `ecommerce-tools` header menu with `ADMIN_TOOLS_FILTER_HEADER_MENUS`.

The menu contains items added through `ADMIN_TOOLS_FILTER_HEADER_MENU_ITEMS`:

- Orders: `orders.index`
- Payments: `payment.index`
- Products: `products.index`
- Customers: `customers.index`

Each item checks its own permission and route.

## Add to the Ecommerce menu

Other plugins can add items to `ecommerce-tools`.

```php
add_filter(ADMIN_TOOLS_FILTER_HEADER_MENU_ITEMS, function (array $items, string $menuId): array {
    if ($menuId !== 'ecommerce-tools') {
        return $items;
    }

    $items[] = [
        'id' => 'my-plugin-ecommerce-report',
        'label' => 'My report',
        'route' => 'my-plugin.ecommerce.report',
        'icon' => 'ti ti-chart-bar',
        'permission' => 'my-plugin.ecommerce.report',
    ];

    return $items;
}, 20, 2);
```

## Order notification

Order notifications are added through `ADMIN_TOOLS_FILTER_HEADER_NOTIFICATIONS`.

The built-in query looks for finished pending orders:

```php
Order::query()
    ->where('status', OrderStatusEnum::PENDING)
    ->where('is_finished', 1);
```

The dropdown links to `orders.edit` when the user has edit permission. Otherwise, it falls back to `orders.index`.

## Safety

The integration catches unexpected Ecommerce errors and skips rendering the notification instead of breaking the admin header.
