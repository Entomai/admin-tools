# Notifications

Standard notifications give plugins a consistent header UI without writing custom dropdown markup.

Use `ADMIN_TOOLS_FILTER_HEADER_NOTIFICATIONS`.

## Basic example

```php
add_filter(ADMIN_TOOLS_FILTER_HEADER_NOTIFICATIONS, function (array $notifications): array {
    $notifications[] = [
        'id' => 'support-pro-tickets',
        'type' => 'support-pro',
        'priority' => 50,
        'permission' => 'support-pro.tickets.index',
        'icon' => 'ti ti-headset',
        'color' => 'red',
        'count' => $pendingTicketsCount,
        'title' => 'Support',
        'description' => 'Pending tickets',
        'empty_message' => 'No pending tickets.',
        'view_all_route' => 'support-pro.tickets.index',
        'items' => [
            [
                'title' => '#1234 John Doe',
                'subtitle' => 'Billing issue',
                'description' => 'Customer is waiting for a reply',
                'route' => ['support-pro.tickets.edit', $ticket->id],
                'time' => $ticket->created_at,
                'avatar' => $ticket->customer_avatar_url,
                'icon' => 'ti ti-ticket',
                'meta' => 'Open ticket',
            ],
        ],
    ];

    return $notifications;
});
```

## Empty state

Notifications are allowed to render with `count` set to `0`. If no items are available, the dropdown shows `empty_message`.

This is useful when you want the user to know that the integration is active even when there are no pending records.

## Colors

Supported color modifiers:

- `blue`
- `teal`
- `amber`
- `red`
- `green`
- `cyan`
- `gray`

## AJAX metadata

The notification schema accepts an optional `ajax` array:

```php
'ajax' => [
    'url' => route('support-pro.notifications.index'),
    'interval' => 60000,
],
```

Admin Tools writes these values to data attributes. A plugin can attach its own refresh behavior without replacing the base markup.
