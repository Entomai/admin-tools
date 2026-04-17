# Admin Tools Fast Menu

`admin-tools` agrega una zona izquierda en el header del admin de Botble. El plugin renderiza esa zona usando el hook nativo `BASE_FILTER_TOP_HEADER_LAYOUT` y luego la mueve con JavaScript al lado del logo.

Esta zona tiene tres partes:

- Fast menu: un unico dropdown con links estructurados y soporte de submenus.
- Header menus: dropdowns extra con el mismo diseno y logica del Fast menu.
- Header notifications: notificaciones estructuradas con icono, contador, dropdown y estado vacio.
- Header left items: HTML, iconos, dropdowns o bloques propios cuando se necesita algo custom.
- Notificaciones integradas: Ecommerce, Contact y Payment se agregan automaticamente si sus plugins estan activos, sus clases existen, sus rutas existen y el usuario tiene permisos.

Orden visual del header izquierdo:

- Menus: Fast menu y todos los menus registrados con `ADMIN_TOOLS_FILTER_HEADER_MENUS`.
- Notifications: todas las notificaciones registradas con `ADMIN_TOOLS_FILTER_HEADER_NOTIFICATIONS`.
- Custom: elementos libres registrados con `ADMIN_TOOLS_FILTER_HEADER_LEFT_ITEMS`.

No hardcodee `/admin` en URLs. Use siempre `route('nombre.de.ruta')`; Botble agrega el prefijo admin configurado.

## Hooks

### Agregar Items Al Fast Menu

Use `ADMIN_TOOLS_FILTER_FAST_MENU_ITEMS` para agregar links o submenus dentro del dropdown del fast menu.

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
                'url' => route('my-plugin.reports.index'),
                'icon' => 'ti ti-chart-bar',
                'permission' => 'my-plugin.reports.index',
            ],
        ],
    ];

    return $items;
});
```

### Agregar Un Menu Propio Al Header

Use `ADMIN_TOOLS_FILTER_HEADER_MENUS` para declarar un menu adicional con el mismo diseno del Fast menu.

```php
add_filter(ADMIN_TOOLS_FILTER_HEADER_MENUS, function (array $menus): array {
    $menus['commerce-tools'] = [
        'label' => 'Commerce',
        'icon' => 'ti ti-shopping-cart',
        'priority' => 60,
        'permission' => 'orders.index',
        'items' => [
            [
                'id' => 'commerce-orders',
                'label' => 'Orders',
                'route' => 'orders.index',
                'icon' => 'ti ti-package',
                'permission' => 'orders.index',
            ],
        ],
    ];

    return $menus;
});
```

### Agregar Items A Un Menu Existente

Use `ADMIN_TOOLS_FILTER_HEADER_MENU_ITEMS` cuando varios plugins deben agregar links al mismo menu por identificador. El callback recibe `items`, `menuId` y la definicion del menu. Registre el filtro con `3` argumentos.

```php
add_filter(ADMIN_TOOLS_FILTER_HEADER_MENU_ITEMS, function (array $items, string $menuId, array $menu): array {
    if ($menuId !== 'commerce-tools') {
        return $items;
    }

    $items[] = [
        'id' => 'commerce-reports',
        'label' => 'Reports',
        'route' => 'ecommerce.report.index',
        'icon' => 'ti ti-chart-bar',
        'permission' => 'ecommerce.report.index',
    ];

    return $items;
}, 20, 3);
```

El menu solo se renderiza si despues de aplicar `ADMIN_TOOLS_FILTER_HEADER_MENU_ITEMS` tiene al menos un item visible para el usuario.

### Agregar Notificaciones Estandar

Use `ADMIN_TOOLS_FILTER_HEADER_NOTIFICATIONS` para notificaciones que deben respetar el diseno del header. No pase HTML aqui; pase datos.

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
        'ajax' => [
            'url' => route('support-pro.notifications.index'),
            'interval' => 60000,
        ],
    ];

    return $notifications;
});
```

`ajax` deja los datos en atributos HTML para que el plugin pueda conectar refresco dinamico sin cambiar el markup base.

Colores soportados por defecto: `blue`, `teal`, `amber`, `red`, `green`, `cyan`, `gray`.

### Agregar Elementos Al Header Izquierdo

Use `ADMIN_TOOLS_FILTER_HEADER_LEFT_ITEMS` para agregar elementos al lado del fast menu cuando necesita control total del HTML.

Ejemplo usando una vista:

```php
add_filter(ADMIN_TOOLS_FILTER_HEADER_LEFT_ITEMS, function (array $items): array {
    $items[] = [
        'id' => 'ecommerce-orders-notification',
        'priority' => 20,
        'permission' => 'orders.index',
        'view' => 'plugins/ecommerce::orders.partials.header-notification',
        'data' => [
            'orders' => $orders,
        ],
    ];

    return $items;
});
```

Ejemplo usando HTML ya renderizado:

```php
add_filter(ADMIN_TOOLS_FILTER_HEADER_LEFT_ITEMS, function (array $items): array {
    $items[] = [
        'id' => 'contact-messages-notification',
        'priority' => 30,
        'permission' => 'contacts.index',
        'html' => view('plugins/contact::partials.header-left-notification', [
            'contacts' => $contacts,
        ])->render(),
    ];

    return $items;
});
```

Ejemplo usando un item estructurado con submenu:

```php
add_filter(ADMIN_TOOLS_FILTER_HEADER_LEFT_ITEMS, function (array $items): array {
    $items[] = [
        'id' => 'my-plugin-alerts',
        'priority' => 40,
        'label' => 'Alerts',
        'icon' => 'ti ti-bell',
        'children' => [
            [
                'id' => 'my-plugin-alerts-index',
                'label' => 'View alerts',
                'url' => route('my-plugin.alerts.index'),
                'icon' => 'ti ti-list',
                'permission' => 'my-plugin.alerts.index',
            ],
        ],
    ];

    return $items;
});
```

## Estructura De Un Item

Los items estructurados soportan:

- `id`: ID unico para HTML/menu.
- `label` o `name`: texto visible.
- `url`: URL destino. Preferir `route('nombre')` para que Botble use el prefijo admin.
- `route`: nombre de ruta, o array `[nombre, parametros]`, como alternativa a `url`.
- `icon`: clase de icono Tabler, por ejemplo `ti ti-settings`.
- `permission` o `permissions`: permiso Botble requerido para mostrar el item.
- `children`: items hijos para submenus.
- `target`: target del link.
- `rel`: atributo rel del link.
- `class`: clase extra para el wrapper.
- `priority`: orden para `ADMIN_TOOLS_FILTER_HEADER_LEFT_ITEMS`.
- `view`: vista Blade para renderizar HTML en el header izquierdo.
- `data`: datos enviados a la vista.
- `html`: HTML directo para renderizar en el header izquierdo.

Divisores dentro del fast menu:

```php
$items[] = ['type' => 'divider'];
```

## Fast Menu Por Defecto

El fast menu por defecto registra solo rutas que existen en la instalacion actual:

- Pages: `pages.index`
- Posts: `posts.index`
- Plugins
- Installed: `plugins.index`
- Add new: `plugins.new`, con permiso `plugins.marketplace`
- Themes
- Themes: `theme.index`
- Theme options: `theme.options`
- Widgets: `widgets.index`
- Settings: `settings.index`
- Admin config: `system.index`

Todas las URLs se generan con `route()`, entonces respetan el prefijo admin configurado en Botble.

## Notificaciones Integradas

`admin-tools` incluye notificaciones compactas para plugins comunes:

- Ecommerce: ordenes pendientes, requiere plugin `ecommerce`, ruta `orders.index` y permiso `orders.index`.
- Contact: mensajes sin leer, requiere plugin `contact`, ruta `contacts.index` y permiso `contacts.index`.
- Payment: pagos pendientes, requiere plugin `payment`, ruta `payment.index` y permiso `payment.index`.

Cada notificacion se muestra como un icono pequeno con contador y color propio. Al abrirla se muestra una lista con los ultimos items y un link para ver todo. Si no hay items pendientes, igual se muestra con contador `0` y un estado vacio para confirmar que la integracion esta cargada.

Las notificaciones de Ecommerce orders y Payment se registran desde `ADMIN_TOOLS_FILTER_HEADER_NOTIFICATIONS`, para que el propio plugin use el mismo contrato disponible para otros plugins.

## Menu Ecommerce Integrado

`admin-tools` registra un menu extra `ecommerce-tools` usando `ADMIN_TOOLS_FILTER_HEADER_MENUS`. El menu solo existe cuando el plugin `ecommerce` esta activo.

Sus items se agregan usando `ADMIN_TOOLS_FILTER_HEADER_MENU_ITEMS`, entonces otros plugins pueden apuntar al mismo identificador `ecommerce-tools`.

Items integrados:

- Orders: `orders.index`
- Payments: `payment.index`
- Products: `products.index`
- Customers: `customers.index`

Cada item tambien valida permiso y existencia de ruta antes de renderizarse.

## Recomendaciones

- Registre estos hooks desde el `HookServiceProvider` del plugin que agrega el item.
- Use guards de permisos para no mostrar accesos que el usuario no puede abrir.
- Use `ADMIN_TOOLS_FILTER_HEADER_LEFT_ITEMS` para elementos visuales del header izquierdo.
- Use `ADMIN_TOOLS_FILTER_FAST_MENU_ITEMS` para links dentro del fast menu.
- Evite usar `BASE_FILTER_TOP_HEADER_LAYOUT` directamente para elementos izquierdos; `admin-tools` se encarga de mover y alinear la zona.
