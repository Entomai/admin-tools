# Blog integration

Admin Tools supports Blog through the default Fast menu.

When the Blog plugin is active and the `posts.index` route exists, Admin Tools adds:

- Posts: `posts.index`

The item also checks the `posts.index` permission before rendering.

## Add more Blog shortcuts

Use `ADMIN_TOOLS_FILTER_FAST_MENU_ITEMS` for global shortcuts.

```php
add_filter(ADMIN_TOOLS_FILTER_FAST_MENU_ITEMS, function (array $items): array {
    $items[] = [
        'id' => 'blog-categories',
        'label' => 'Blog categories',
        'route' => 'categories.index',
        'icon' => 'ti ti-category',
        'permission' => 'categories.index',
    ];

    return $items;
});
```

## Blog-specific menu

If a site needs more than one Blog shortcut, create a separate header menu:

```php
add_filter(ADMIN_TOOLS_FILTER_HEADER_MENUS, function (array $menus): array {
    $menus['blog-tools'] = [
        'label' => 'Blog',
        'icon' => 'ti ti-article',
        'priority' => 70,
        'items' => [],
    ];

    return $menus;
});
```

Then add items with `ADMIN_TOOLS_FILTER_HEADER_MENU_ITEMS`.
