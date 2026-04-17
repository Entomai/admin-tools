# Custom header content

Use `ADMIN_TOOLS_FILTER_HEADER_LEFT_ITEMS` when the structured menu or notification APIs are not enough.

This hook is an escape hatch. Prefer structured hooks when possible because they keep the header visually consistent.

## Add a Blade view

```php
add_filter(ADMIN_TOOLS_FILTER_HEADER_LEFT_ITEMS, function (array $items): array {
    $items[] = [
        'id' => 'my-plugin-header-widget',
        'section' => 'custom',
        'priority' => 100,
        'permission' => 'my-plugin.index',
        'view' => 'plugins/my-plugin::partials.header-widget',
        'data' => [
            'count' => $count,
        ],
    ];

    return $items;
});
```

## Add rendered HTML

```php
add_filter(ADMIN_TOOLS_FILTER_HEADER_LEFT_ITEMS, function (array $items): array {
    $items[] = [
        'id' => 'my-plugin-html',
        'priority' => 100,
        'html' => view('plugins/my-plugin::partials.header-html')->render(),
    ];

    return $items;
});
```

## Add a simple structured item

```php
add_filter(ADMIN_TOOLS_FILTER_HEADER_LEFT_ITEMS, function (array $items): array {
    $items[] = [
        'id' => 'my-plugin-link',
        'label' => 'My plugin',
        'icon' => 'ti ti-apps',
        'route' => 'my-plugin.index',
        'permission' => 'my-plugin.index',
    ];

    return $items;
});
```

## Section

Custom items default to the `custom` section, which renders after menus and notifications.

Do not use `section` to force unrelated content into the menu or notification area unless the UI has a strong reason.
