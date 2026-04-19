# Custom Update Providers

Custom providers use two hooks:

- `ADMIN_TOOLS_FILTER_UPDATE_ITEMS` adds selectable update rows to the header update widget.
- `ADMIN_TOOLS_FILTER_INSTALL_UPDATE_ITEM` installs a selected custom update.

## Add Update Rows

```php
add_filter(ADMIN_TOOLS_FILTER_UPDATE_ITEMS, function (array $items): array {
    if (! auth()->user()?->hasPermission('plugins.index')) {
        return $items;
    }

    $items[] = [
        'key' => 'plugin:vendor-api:my-plugin',
        'type' => 'plugin',
        'source' => 'vendor_api',
        'source_label' => 'Vendor API',
        'slug' => 'my-plugin',
        'name' => 'My Plugin',
        'current_version' => '1.0.0',
        'latest_version' => '1.1.0',
        'summary' => 'Security and compatibility fixes',
        'released_at' => '2026-04-18',
        'icon' => 'ti ti-plug',
        'payload' => [
            'download_url' => 'https://example.com/my-plugin-1.1.0.zip',
        ],
    ];

    return $items;
});
```

## Install A Custom Row

```php
add_filter(ADMIN_TOOLS_FILTER_INSTALL_UPDATE_ITEM, function (mixed $result, array $item): mixed {
    if (($item['source'] ?? null) !== 'vendor_api') {
        return $result;
    }

    if (($item['slug'] ?? null) !== 'my-plugin') {
        return [
            'error' => true,
            'message' => 'Invalid update item.',
        ];
    }

    // Download to storage, validate the ZIP, backup current files,
    // replace files, run migrations, publish assets, and clear cache.

    return [
        'error' => false,
        'message' => 'My Plugin was updated successfully.',
    ];
}, 20, 2);
```

## Required Item Fields

- `key`: unique stable key, usually `type:provider:slug`.
- `type`: `plugin` or `theme`.
- `source`: provider identifier.
- `source_label`: human label shown in the widget.
- `slug`: plugin folder or theme folder.
- `name`: display name.
- `current_version`: installed version.
- `latest_version`: available version.

Optional fields:

- `summary`
- `released_at`
- `icon`
- `url`
- `payload`
