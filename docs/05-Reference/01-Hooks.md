# Hooks reference

Admin Tools exposes public filters for header composition and the header update widget.

## ADMIN_TOOLS_FILTER_FAST_MENU_ITEMS

Adds items to the default Fast menu.

```php
add_filter(ADMIN_TOOLS_FILTER_FAST_MENU_ITEMS, function (array $items): array {
    return $items;
});
```

## ADMIN_TOOLS_FILTER_HEADER_MENUS

Declares additional header menus.

```php
add_filter(ADMIN_TOOLS_FILTER_HEADER_MENUS, function (array $menus): array {
    return $menus;
});
```

## ADMIN_TOOLS_FILTER_HEADER_MENU_ITEMS

Adds items to a specific header menu by ID.

Register with `3` accepted arguments.

```php
add_filter(ADMIN_TOOLS_FILTER_HEADER_MENU_ITEMS, function (array $items, string $menuId, array $menu): array {
    return $items;
}, 20, 3);
```

## ADMIN_TOOLS_FILTER_HEADER_NOTIFICATIONS

Adds structured notifications to the notification section.

```php
add_filter(ADMIN_TOOLS_FILTER_HEADER_NOTIFICATIONS, function (array $notifications): array {
    return $notifications;
});
```

## ADMIN_TOOLS_FILTER_HEADER_LEFT_ITEMS

Adds custom content to the header-left zone.

Use this only when menu and notification structures are not enough.

```php
add_filter(ADMIN_TOOLS_FILTER_HEADER_LEFT_ITEMS, function (array $items): array {
    return $items;
});
```

## ADMIN_TOOLS_FILTER_UPDATE_ITEMS

Adds selectable update rows to the Admin Tools header update widget.

```php
add_filter(ADMIN_TOOLS_FILTER_UPDATE_ITEMS, function (array $items): array {
    $items[] = [
        'key' => 'plugin:custom-provider:my-plugin',
        'type' => 'plugin',
        'source' => 'my_provider',
        'source_label' => 'My Provider',
        'slug' => 'my-plugin',
        'name' => 'My Plugin',
        'current_version' => '1.0.0',
        'latest_version' => '1.1.0',
        'summary' => 'Maintenance update',
        'icon' => 'ti ti-plug',
    ];

    return $items;
});
```

## ADMIN_TOOLS_FILTER_INSTALL_UPDATE_ITEM

Handles installation for custom update rows. Return `null` when the selected row does not belong to your provider.

```php
add_filter(ADMIN_TOOLS_FILTER_INSTALL_UPDATE_ITEM, function (mixed $result, array $item): mixed {
    if (($item['source'] ?? null) !== 'my_provider') {
        return $result;
    }

    // Download, validate, backup, replace files, run migrations, publish assets.

    return [
        'error' => false,
        'message' => 'My Plugin was updated.',
    ];
}, 20, 2);
```

## Core hook used internally

Admin Tools renders into Botble with:

```php
BASE_FILTER_TOP_HEADER_LAYOUT
```

Other plugins should avoid using that core hook directly for left-side header content. Use Admin Tools filters instead.
