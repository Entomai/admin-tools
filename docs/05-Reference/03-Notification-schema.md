# Notification schema

Notifications are structured arrays rendered by Admin Tools.

## Notification fields

| Field | Type | Description |
| --- | --- | --- |
| `id` | string | Stable notification identifier. |
| `type` | string | Public type written to `data-entomai-notification`. |
| `priority` | int | Sort order inside the notification section. |
| `permission` | string or array | Required Botble permission. |
| `icon` | string | Tabler icon class. |
| `color` | string | Color modifier. |
| `count` | int | Badge counter. |
| `title` | string | Dropdown title. |
| `description` | string | Short dropdown subtitle. |
| `empty_message` | string | Message shown when `items` is empty. |
| `view_all_url` | string | URL for the header action. |
| `view_all_route` | string or array | Route alternative for `view_all_url`. |
| `view_all_label` | string | Header action label. |
| `items` | array | Dropdown items. |
| `ajax` | array | Optional refresh metadata. |

## Item fields

| Field | Type | Description |
| --- | --- | --- |
| `id` | string | Stable item identifier. |
| `title` | string | Main line. |
| `subtitle` | string | Alternative detail text. |
| `description` | string | Secondary line. |
| `url` | string | Item URL. |
| `route` | string or array | Route alternative for `url`. |
| `time` | DateTime | Rendered with `diffForHumans()`. |
| `avatar` | string | Avatar image URL. |
| `icon` | string | Fallback icon when no avatar exists. |
| `meta` | string | Small extra line. |

## Colors

Supported defaults:

- `blue`
- `teal`
- `amber`
- `red`
- `green`
- `cyan`
- `gray`

Unknown values are sanitized into CSS modifiers.

## AJAX

Admin Tools does not force a polling implementation. It exposes metadata for plugin-specific JavaScript:

```php
'ajax' => [
    'url' => route('my-plugin.notifications.index'),
    'interval' => 60000,
],
```

The notification view writes these values as data attributes.
