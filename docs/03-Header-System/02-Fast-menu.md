# Fast menu

The Fast menu is the default dropdown shipped by Admin Tools. It is intended for global admin shortcuts that are useful across the back office.

## Default items

Admin Tools adds default items only when their routes exist:

- Pages: `pages.index`
- Posts: `posts.index`
- Plugins
- Installed: `plugins.index`
- Add new: `plugins.new`
- Themes
- Themes: `theme.index`
- Theme options: `theme.options`
- Widgets: `widgets.index`
- Settings: `settings.index`
- Admin config: `system.index`

## Add items

Use `ADMIN_TOOLS_FILTER_FAST_MENU_ITEMS`.

```php
add_filter(ADMIN_TOOLS_FILTER_FAST_MENU_ITEMS, function (array $items): array {
    $items[] = [
        'id' => 'my-plugin-fast-menu',
        'label' => 'My plugin',
        'icon' => 'ti ti-tool',
        'children' => [
            [
                'id' => 'my-plugin-reports',
                'label' => 'Reports',
                'route' => 'my-plugin.reports.index',
                'icon' => 'ti ti-chart-bar',
                'permission' => 'my-plugin.reports.index',
            ],
        ],
    ];

    return $items;
});
```

## Dividers

Use a divider to separate groups inside the dropdown:

```php
$items[] = ['type' => 'divider'];
```

Admin Tools removes duplicate or trailing dividers during normalization.

## Submenus

Any item with `children` becomes a submenu. Submenus can be nested, and the same renderer is reused by custom header menus.
