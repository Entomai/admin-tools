# Menu item schema

Menu items are used by the Fast menu, custom header menus, and structured custom header-left items.

## Supported fields

| Field | Type | Description |
| --- | --- | --- |
| `id` | string | Stable item identifier. |
| `label` | string | Visible text. |
| `name` | string | Alternative to `label`. |
| `url` | string | Absolute or generated URL. |
| `route` | string or array | Route name, or `[name, parameters]`. |
| `icon` | string | Tabler icon class, such as `ti ti-settings`. |
| `permission` | string or array | Required Botble permission. |
| `permissions` | string or array | Alternative to `permission`. |
| `children` | array | Child menu items. |
| `target` | string | Link target. |
| `rel` | string | Link rel attribute. |
| `class` | string | Extra CSS class. |
| `type` | string | Use `divider` for a divider. |

## Route example

```php
[
    'id' => 'products',
    'label' => 'Products',
    'route' => 'products.index',
    'icon' => 'ti ti-box',
    'permission' => 'products.index',
]
```

## Route with parameters

```php
[
    'id' => 'edit-product',
    'label' => 'Edit product',
    'route' => ['products.edit', $product->id],
]
```

## Submenu example

```php
[
    'id' => 'reports',
    'label' => 'Reports',
    'icon' => 'ti ti-chart-bar',
    'children' => [
        [
            'id' => 'sales-report',
            'label' => 'Sales',
            'route' => 'reports.sales',
        ],
    ],
]
```

## Normalization

Admin Tools removes items when:

- the item is not an array
- the required permission is missing
- the route does not exist
- the item has no URL and no visible children
- the item has no label for a dropdown entry
