# Custom header menus

Header menus are dropdowns that use the same UI and submenu logic as the default Fast menu. Use them when a plugin needs its own menu beside the Fast menu.

## Declare a menu

Use `ADMIN_TOOLS_FILTER_HEADER_MENUS`.

```php
add_filter(ADMIN_TOOLS_FILTER_HEADER_MENUS, function (array $menus): array {
    $menus['commerce-tools'] = [
        'label' => 'Commerce',
        'icon' => 'ti ti-shopping-cart',
        'priority' => 60,
        'permission' => 'orders.index',
        'items' => [],
    ];

    return $menus;
});
```

The array key can act as the menu ID. You can also pass an explicit `id`.

## Add items by menu ID

Use `ADMIN_TOOLS_FILTER_HEADER_MENU_ITEMS` when more than one plugin should contribute to the same menu.

Register the filter with `3` accepted arguments.

```php
add_filter(ADMIN_TOOLS_FILTER_HEADER_MENU_ITEMS, function (array $items, string $menuId, array $menu): array {
    if ($menuId !== 'commerce-tools') {
        return $items;
    }

    $items[] = [
        'id' => 'commerce-orders',
        'label' => 'Orders',
        'route' => 'orders.index',
        'icon' => 'ti ti-package',
        'permission' => 'orders.index',
    ];

    return $items;
}, 20, 3);
```

## Render rule

A menu is rendered only when it has at least one visible item after permissions, route checks, and child normalization.

## Recommended IDs

Use stable, plugin-like IDs:

- `ecommerce-tools`
- `support-tools`
- `marketing-tools`
- `billing-tools`

Stable IDs let other plugins add items safely.
