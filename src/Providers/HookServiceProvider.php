<?php

namespace Botble\AdminTools\Providers;

use Botble\AdminTools\Services\AdminToolsCacheService;
use Botble\AdminTools\Services\AdminToolsUpdateService;
use Botble\Base\Facades\Assets;
use Botble\Base\Facades\BaseHelper;
use Botble\Base\Supports\ServiceProvider;
use Botble\Setting\Facades\Setting;
use DOMDocument;
use DOMElement;
use Illuminate\Contracts\Support\Htmlable;
use Illuminate\Routing\Events\RouteMatched;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Str;
use Throwable;

class HookServiceProvider extends ServiceProvider
{
    public function boot(): void
    {
        if (! BaseHelper::isAdminRequest()) {
            return;
        }

        $this->app['events']->listen(RouteMatched::class, function (): void {
            $this->registerHeaderViewOverrides();

            $assetVersion = @filemtime(__DIR__.'/../../public/js/fast-menu.js') ?: get_cms_version();

            Assets::addStylesDirectly('vendor/core/plugins/admin-tools/css/fast-menu.css?v='.$assetVersion)
                ->addScriptsDirectly('vendor/core/plugins/admin-tools/js/fast-menu.js?v='.$assetVersion);

            add_filter(ADMIN_TOOLS_FILTER_HEADER_MENUS, [$this, 'registerEcommerceHeaderMenu']);
            add_filter(ADMIN_TOOLS_FILTER_HEADER_MENU_ITEMS, [$this, 'registerEcommerceHeaderMenuItems'], 20, 3);
            add_filter(ADMIN_TOOLS_FILTER_HEADER_NOTIFICATIONS, [$this, 'registerEcommerceHeaderNotifications']);
            add_filter(BASE_FILTER_TOP_HEADER_LAYOUT, [$this, 'renderHeaderLeft'], 90);
            add_filter(BASE_FILTER_TOP_HEADER_LAYOUT, [$this, 'filterTopHeaderLayout'], 1000);
            add_filter(BASE_FILTER_HEAD_LAYOUT_TEMPLATE, [$this, 'renderAdminAppearanceHead'], 40);
            add_filter(BASE_FILTER_HEADER_LAYOUT_TEMPLATE, [$this, 'renderAdminAppearanceBodyScript'], 1);
        });
    }

    protected function registerHeaderViewOverrides(): void
    {
        $path = __DIR__.'/../../resources/views/vendor/core/base';

        if (is_dir($path)) {
            view()->prependNamespace('core/base', $path);
        }
    }

    public function registerEcommerceHeaderMenu(array $menus): array
    {
        if (
            ! admin_tools_setting_bool('ecommerce_header_menu_enabled', true)
            || ! $this->isPluginReady('ecommerce')
        ) {
            return $menus;
        }

        $menus['ecommerce-tools'] = array_merge([
            'label' => trans('plugins/admin-tools::admin-tools.ecommerce_menu'),
            'icon' => 'ti ti-shopping-cart',
            'priority' => 60,
            'items' => [],
        ], $menus['ecommerce-tools'] ?? []);

        return $menus;
    }

    public function registerEcommerceHeaderMenuItems(array $items, string $menuId, array $menu = []): array
    {
        if (
            $menuId !== 'ecommerce-tools'
            || ! admin_tools_setting_bool('ecommerce_header_menu_enabled', true)
            || ! $this->isPluginReady('ecommerce')
        ) {
            return $items;
        }

        return array_merge($items, [
            [
                'id' => 'admin-tools-ecommerce-orders',
                'label' => trans('plugins/admin-tools::admin-tools.orders'),
                'route' => 'orders.index',
                'icon' => 'ti ti-package',
                'permission' => 'orders.index',
            ],
            [
                'id' => 'admin-tools-ecommerce-payments',
                'label' => trans('plugins/admin-tools::admin-tools.payments'),
                'route' => 'payment.index',
                'icon' => 'ti ti-credit-card',
                'permission' => 'payment.index',
            ],
            [
                'id' => 'admin-tools-ecommerce-products',
                'label' => trans('plugins/admin-tools::admin-tools.products'),
                'route' => 'products.index',
                'icon' => 'ti ti-box',
                'permission' => 'products.index',
            ],
            [
                'id' => 'admin-tools-ecommerce-customers',
                'label' => trans('plugins/admin-tools::admin-tools.customers'),
                'route' => 'customers.index',
                'icon' => 'ti ti-users',
                'permission' => 'customers.index',
            ],
        ]);
    }

    public function registerEcommerceHeaderNotifications(array $notifications): array
    {
        if (admin_tools_setting_bool('ecommerce_notifications_enabled', true) && ($notification = $this->getEcommerceNotification())) {
            $notifications[] = $notification;
        }

        if (admin_tools_setting_bool('payment_notifications_enabled', true) && ($notification = $this->getPaymentNotification())) {
            $notifications[] = $notification;
        }

        return $notifications;
    }

    public function renderHeaderLeft(?string $html): ?string
    {
        if (! Auth::guard()->check()) {
            return $html;
        }

        $settings = $this->getHeaderSettings();
        $fastMenuItems = [];

        if ($settings['fast_menu_enabled']) {
            $fastMenuItems = apply_filters(
                ADMIN_TOOLS_FILTER_FAST_MENU_ITEMS,
                $this->getDefaultItems()
            );

            if (is_array($fastMenuItems)) {
                $seenIds = [];
                $fastMenuItems = array_filter($fastMenuItems, function ($item) use (&$seenIds) {
                    if (! is_array($item)) {
                        return true;
                    }

                    $id = $item['id'] ?? null;

                    if ($id) {
                        if (in_array($id, $seenIds)) {
                            return false;
                        }
                        $seenIds[] = $id;
                    }

                    return true;
                });
            }

            $fastMenuItems = $this->normalizeItems(is_array($fastMenuItems) ? $fastMenuItems : []);
        }

        $headerLeftItems = apply_filters(ADMIN_TOOLS_FILTER_HEADER_LEFT_ITEMS, array_merge(
            $this->getHeaderMenuItems(),
            $this->getHeaderCacheCleanerItems(),
            $this->getHeaderNotificationItems(),
            $this->getHeaderUpdateItems()
        ));
        $headerLeftItems = $this->normalizeHeaderLeftItems(is_array($headerLeftItems) ? $headerLeftItems : []);

        if ($fastMenuItems === [] && $headerLeftItems === []) {
            return $html;
        }

        return $html.view('plugins/admin-tools::header-left.index', compact('fastMenuItems', 'headerLeftItems', 'settings'))->render();
    }

    public function filterTopHeaderLayout(?string $html): ?string
    {
        if (! is_string($html) || trim($html) === '') {
            return $html;
        }

        $fragments = $this->splitTopHeaderFragments($html);

        if ($fragments === []) {
            return $html;
        }

        $hiddenItems = $this->hiddenHeaderHookItemIds();
        $hideBotbleNotification = admin_tools_setting_bool('hide_botble_notification', false);
        $catalogItems = [];
        $filteredFragments = [];

        foreach ($fragments as $fragment) {
            $fragmentHtml = $fragment['html'];
            $descriptor = $this->describeTopHeaderFragment($fragmentHtml, $fragment['element']);

            if ($descriptor) {
                $id = $descriptor['id'];

                if (! $this->shouldSkipFromHeaderHookCatalog($id)) {
                    $catalogItems[$id] = ['label' => $descriptor['label']];
                }

                if (
                    ($id === 'botble-admin-notification' && $hideBotbleNotification)
                    || in_array($id, $hiddenItems, true)
                ) {
                    continue;
                }
            }

            $filteredFragments[] = $fragmentHtml;
        }

        $this->rememberHeaderHookItems($catalogItems);

        return implode('', $filteredFragments);
    }

    public function renderAdminAppearanceHead(?string $html): string
    {
        return admin_tools_render_admin_appearance_head($html);
    }

    public function renderAdminAppearanceBodyScript(?string $html): string
    {
        return admin_tools_render_admin_appearance_body_script($html);
    }

    protected function splitTopHeaderFragments(string $html): array
    {
        if (! class_exists(DOMDocument::class)) {
            return [['html' => $html, 'element' => null]];
        }

        $document = new DOMDocument('1.0', 'UTF-8');
        $previous = libxml_use_internal_errors(true);
        $loaded = $document->loadHTML(
            '<!DOCTYPE html><html><head><meta charset="utf-8"></head><body><div id="admin-tools-top-header-fragments">'.$html.'</div></body></html>'
        );
        libxml_clear_errors();
        libxml_use_internal_errors($previous);

        if (! $loaded || ! ($wrapper = $document->getElementById('admin-tools-top-header-fragments'))) {
            return [['html' => $html, 'element' => null]];
        }

        $fragments = [];

        foreach ($wrapper->childNodes as $node) {
            $fragmentHtml = $document->saveHTML($node);

            if (! is_string($fragmentHtml) || trim($fragmentHtml) === '') {
                continue;
            }

            $fragments[] = [
                'html' => $fragmentHtml,
                'element' => $node instanceof DOMElement ? $node : null,
            ];
        }

        return $fragments;
    }

    protected function describeTopHeaderFragment(string $html, ?DOMElement $element = null): ?array
    {
        if (str_contains($html, 'data-entomai-header-left')) {
            return [
                'id' => 'admin-tools-header-left',
                'label' => trans('plugins/admin-tools::admin-tools.admin_tools_settings'),
            ];
        }

        if ($this->isBotbleNotificationFragment($html)) {
            return [
                'id' => 'botble-admin-notification',
                'label' => trans('plugins/admin-tools::admin-tools.header_hook_item_botble_notification'),
            ];
        }

        if ($this->isHotelBookingNotificationFragment($html)) {
            return [
                'id' => 'hotel-booking-notification',
                'label' => trans('plugins/admin-tools::admin-tools.header_hook_item_hotel_booking_notification'),
            ];
        }

        $id = $this->getHeaderFragmentId($html, $element);
        $label = $this->getHeaderFragmentLabel($html, $element);

        return [
            'id' => $id,
            'label' => $label ?: trans('plugins/admin-tools::admin-tools.header_hook_item_unknown', ['id' => Str::after($id, 'rendered-')]),
        ];
    }

    protected function isBotbleNotificationFragment(string $html): bool
    {
        return str_contains($html, 'notification-sidebar')
            || (str_contains($html, 'notification-count') && str_contains($html, 'icon-tabler-bell'));
    }

    protected function isHotelBookingNotificationFragment(string $html): bool
    {
        try {
            if (Route::has('booking.index') && str_contains($html, route('booking.index'))) {
                return true;
            }
        } catch (Throwable) {
            return false;
        }

        return str_contains($html, 'icon-tabler-mail') && str_contains(Str::lower($html), 'booking');
    }

    protected function getHeaderFragmentId(string $html, ?DOMElement $element = null): string
    {
        if ($element instanceof DOMElement && $element->hasAttribute('id')) {
            return $this->normalizeHtmlId('dom-'.$element->getAttribute('id'));
        }

        preg_match('/icon-tabler-([a-z0-9-]+)/i', $html, $iconMatch);
        preg_match('/href=(["\'])(.*?)\1/i', $html, $hrefMatch);

        $href = $hrefMatch[2] ?? null;

        if ($href) {
            $href = parse_url(html_entity_decode($href, ENT_QUOTES | ENT_HTML5, 'UTF-8'), PHP_URL_PATH) ?: $href;
        }

        $signature = implode('|', array_filter([
            $element instanceof DOMElement ? $element->tagName : null,
            $element instanceof DOMElement ? $element->getAttribute('class') : null,
            $iconMatch[1] ?? null,
            $href,
        ]));

        if ($signature === '') {
            $signature = preg_replace('/\s+/', ' ', trim(strip_tags($html))) ?: $html;
        }

        return 'rendered-'.substr(sha1($signature), 0, 16);
    }

    protected function getHeaderFragmentLabel(string $html, ?DOMElement $element = null): ?string
    {
        foreach (['aria-label', 'title'] as $attribute) {
            if ($element instanceof DOMElement && $element->hasAttribute($attribute)) {
                return trim($element->getAttribute($attribute));
            }
        }

        $text = trim(preg_replace(
            '/\s+/',
            ' ',
            html_entity_decode(strip_tags($html), ENT_QUOTES | ENT_HTML5, 'UTF-8')
        ) ?: '');

        if ($text === '' || is_numeric($text)) {
            return null;
        }

        return Str::limit($text, 80, '');
    }

    protected function filterHiddenHeaderNotifications(array $notifications): array
    {
        $hiddenItems = $this->hiddenHeaderHookItemIds();

        if ($hiddenItems === []) {
            return $notifications;
        }

        return array_values(array_filter($notifications, function (mixed $notification) use ($hiddenItems): bool {
            if (! is_array($notification)) {
                return true;
            }

            $id = $this->getHeaderNotificationId($notification);

            return ! $id || ! in_array($id, $hiddenItems, true);
        }));
    }

    protected function rememberHeaderHookItemsFromNotifications(array $notifications): void
    {
        $items = [];

        foreach ($notifications as $notification) {
            if (! is_array($notification) || ! ($id = $this->getHeaderNotificationId($notification))) {
                continue;
            }

            if ($this->shouldSkipFromHeaderHookCatalog($id)) {
                continue;
            }

            $label = $notification['title'] ?? $notification['label'] ?? $notification['name'] ?? $id;
            $description = $notification['description'] ?? $notification['subtitle'] ?? null;

            if (! is_string($label)) {
                $label = $id;
            }

            if (is_string($description) && $description !== '') {
                $label = trim($label.' - '.$description);
            }

            $items[$id] = ['label' => Str::limit($label, 100, '')];
        }

        $this->rememberHeaderHookItems($items);
    }

    protected function rememberHeaderHookItems(array $items): void
    {
        if ($items === []) {
            return;
        }

        $catalog = admin_tools_setting_array('header_hook_item_catalog');
        $changed = false;

        foreach ($items as $id => $item) {
            if (! is_string($id) || $id === '' || isset($catalog[$id])) {
                continue;
            }

            $catalog[$id] = $item;
            $changed = true;
        }

        if (! $changed) {
            return;
        }

        try {
            Setting::set('admin_tools_header_hook_item_catalog', json_encode($catalog))->save();
        } catch (Throwable) {
            // Do not let discovery metadata break the admin header.
        }
    }

    protected function hiddenHeaderHookItemIds(): array
    {
        return array_values(array_filter(array_map(
            fn (mixed $id): ?string => is_string($id) && $id !== '' ? $this->normalizeHtmlId($id) : null,
            admin_tools_setting_array('hidden_header_hook_items')
        )));
    }

    protected function getHeaderNotificationId(array $notification): ?string
    {
        $id = $notification['id'] ?? $notification['type'] ?? null;

        return is_string($id) && $id !== '' ? $this->normalizeHtmlId($id) : null;
    }

    protected function shouldSkipFromHeaderHookCatalog(string $id): bool
    {
        return in_array($id, [
            'admin-tools-header-left',
            'botble-admin-notification',
            'admin-tools-contact-notification',
            'admin-tools-ecommerce-notification',
            'admin-tools-payment-notification',
        ], true);
    }

    protected function getHeaderSettings(): array
    {
        return [
            'fast_menu_enabled' => admin_tools_setting_bool('fast_menu_enabled', true),
            'update_header_widget_enabled' => admin_tools_setting_bool('update_header_widget_enabled', true),
            'sticky_header_enabled' => admin_tools_setting_bool('sticky_header_enabled', true),
            'compact_brand_enabled' => admin_tools_setting_bool('compact_brand_enabled', true),
            'hide_view_website_button' => admin_tools_setting_bool('hide_view_website_button', false),
            'hide_global_search' => admin_tools_setting_bool('hide_global_search', false),
            'hide_botble_notification' => admin_tools_setting_bool('hide_botble_notification', false),
        ];
    }

    protected function getDefaultItems(): array
    {
        return array_values(array_filter([
            $this->routeItem(
                'pages.index',
                trans('plugins/admin-tools::admin-tools.pages'),
                'ti ti-file-text',
                'pages.index'
            ),
            $this->routeItem(
                'posts.index',
                trans('plugins/admin-tools::admin-tools.posts'),
                'ti ti-article',
                'posts.index'
            ),
            $this->groupItem(
                'admin-tools-fast-menu-plugins',
                trans('plugins/admin-tools::admin-tools.plugins'),
                'ti ti-plug',
                [
                    $this->routeItem(
                        'plugins.index',
                        trans('plugins/admin-tools::admin-tools.installed_plugins'),
                        'ti ti-list-check',
                        'plugins.index'
                    ),
                    $this->routeItem(
                        'plugins.new',
                        trans('plugins/admin-tools::admin-tools.add_new_plugin'),
                        'ti ti-circle-plus',
                        'plugins.marketplace'
                    ),
                ]
            ),
            $this->groupItem(
                'admin-tools-fast-menu-themes',
                trans('plugins/admin-tools::admin-tools.themes'),
                'ti ti-brush',
                [
                    $this->routeItem(
                        'theme.index',
                        trans('plugins/admin-tools::admin-tools.themes'),
                        'ti ti-palette',
                        'theme.index'
                    ),
                    $this->routeItem(
                        'theme.options',
                        trans('plugins/admin-tools::admin-tools.theme_options'),
                        'ti ti-list-tree',
                        'theme.options'
                    ),
                    $this->routeItem(
                        'widgets.index',
                        trans('plugins/admin-tools::admin-tools.widgets'),
                        'ti ti-layout-sidebar',
                        'widgets.index'
                    ),
                ]
            ),
            $this->routeItem(
                'settings.index',
                trans('plugins/admin-tools::admin-tools.settings'),
                'ti ti-settings',
                'settings.index'
            ),
            $this->routeItem(
                'system.index',
                trans('plugins/admin-tools::admin-tools.admin_config'),
                'ti ti-adjustments',
                'system.index'
            ),
            $this->routeItem(
                'admin-tools.settings',
                trans('plugins/admin-tools::admin-tools.admin_tools_settings'),
                'ti ti-bolt',
                'admin-tools.settings'
            ),
        ]));
    }

    protected function groupItem(string $id, string $label, string $icon, array $children): ?array
    {
        $children = array_values(array_filter($children));

        if ($children === []) {
            return null;
        }

        return compact('id', 'label', 'icon', 'children');
    }

    protected function getHeaderNotificationItems(): array
    {
        $notifications = apply_filters(
            ADMIN_TOOLS_FILTER_HEADER_NOTIFICATIONS,
            $this->getDefaultHeaderNotifications()
        );
        $notifications = is_array($notifications) ? $notifications : [];

        $this->rememberHeaderHookItemsFromNotifications($notifications);

        return array_map(
            fn (array $notification): array => $this->notificationItem($notification),
            $this->normalizeHeaderNotifications($this->filterHiddenHeaderNotifications($notifications))
        );
    }

    protected function getDefaultHeaderNotifications(): array
    {
        return array_values(array_filter([
            $this->getContactNotification(),
        ]));
    }

    protected function getHeaderMenuItems(): array
    {
        $menus = apply_filters(ADMIN_TOOLS_FILTER_HEADER_MENUS, []);

        return array_map(
            fn (array $menu): array => [
                'id' => $menu['id'],
                'type' => 'html',
                'section' => 'menu',
                'priority' => $menu['priority'] ?? 100,
                'html' => view('plugins/admin-tools::fast-menu.index', [
                    'items' => $menu['items'],
                    'menu' => $menu,
                ])->render(),
            ],
            $this->normalizeHeaderMenus(is_array($menus) ? $menus : [])
        );
    }

    protected function getHeaderUpdateItems(): array
    {
        if (
            ! admin_tools_setting_bool('update_header_widget_enabled', true)
            || ! Route::has('admin-tools.updates.update')
            || ! $this->userCan('admin-tools.settings')
        ) {
            return [];
        }

        try {
            $state = app(AdminToolsUpdateService::class)->getState();
        } catch (Throwable) {
            return [];
        }

        $updates = $state['items'] ?? [];
        $messages = $state['messages'] ?? [];

        if ($updates === [] && $messages === []) {
            return [];
        }

        return [
            [
                'id' => 'admin-tools-header-updates',
                'section' => 'update',
                'priority' => 10,
                'view' => 'plugins/admin-tools::header-left.updates',
                'data' => compact('state', 'updates', 'messages'),
            ],
        ];
    }

    protected function getHeaderCacheCleanerItems(): array
    {
        if (
            ! admin_tools_setting_bool('fast_cache_cleaner_enabled', true)
            || ! Route::has('admin-tools.cache.clear')
            || ! $this->userCan('superuser')
        ) {
            return [];
        }

        try {
            $cacheService = app(AdminToolsCacheService::class);
        } catch (Throwable) {
            return [];
        }

        return [
            [
                'id' => 'admin-tools-fast-cache-cleaner',
                'section' => 'cache',
                'priority' => 10,
                'view' => 'plugins/admin-tools::header-left.cache-cleaner',
                'data' => [
                    'commands' => $cacheService->commands(),
                    'formattedCacheSize' => $cacheService->formattedCacheSize(),
                ],
            ],
        ];
    }

    protected function getEcommerceNotification(): ?array
    {
        $orderClass = 'Botble\Ecommerce\Models\Order';
        $statusClass = 'Botble\Ecommerce\Enums\OrderStatusEnum';

        if (! $this->isPluginReady('ecommerce', [$orderClass], ['orders.index'])) {
            return null;
        }

        if (! $this->userCan('orders.index')) {
            return null;
        }

        try {
            $status = class_exists($statusClass) ? $statusClass::PENDING : 'pending';
            $query = $orderClass::query()
                ->where('status', $status)
                ->where('is_finished', 1);
            $count = (clone $query)->count();

            $orders = $query
                ->with(['address', 'user'])
                ->latest()
                ->limit(8)
                ->get();
            $canEditOrders = $this->userCan('orders.edit') && Route::has('orders.edit');

            return [
                'id' => 'admin-tools-ecommerce-notification',
                'priority' => 20,
                'type' => 'ecommerce',
                'title' => trans('plugins/admin-tools::admin-tools.notifications_orders_title'),
                'description' => trans('plugins/admin-tools::admin-tools.notifications_orders_description'),
                'icon' => 'ti ti-shopping-cart',
                'color' => 'blue',
                'count' => $count,
                'view_all_url' => route('orders.index'),
                'view_all_label' => trans('plugins/admin-tools::admin-tools.notifications_view_all'),
                'empty_message' => trans('plugins/admin-tools::admin-tools.notifications_orders_empty'),
                'items' => $orders->map(function ($order) use ($canEditOrders): array {
                    $name = $order->address->name ?: $order->user->name;

                    return [
                        'title' => sprintf('#%s %s', $order->code ?: $order->getKey(), $name),
                        'description' => $this->formatMoney($order->amount),
                        'url' => $canEditOrders ? route('orders.edit', $order->getKey()) : route('orders.index'),
                        'time' => $order->created_at,
                        'avatar' => $order->user->id ? $order->user->avatar_url : $order->address->avatar_url,
                        'meta' => trans('plugins/admin-tools::admin-tools.notifications_orders_meta'),
                    ];
                })->all(),
            ];
        } catch (Throwable) {
            return null;
        }
    }

    protected function getContactNotification(): ?array
    {
        $contactClass = 'Botble\Contact\Models\Contact';
        $statusClass = 'Botble\Contact\Enums\ContactStatusEnum';

        if (! admin_tools_setting_bool('contact_notifications_enabled', true)) {
            return null;
        }

        if (! $this->isPluginReady('contact', [$contactClass], ['contacts.index'])) {
            return null;
        }

        if (! $this->userCan('contacts.index')) {
            return null;
        }

        try {
            $status = class_exists($statusClass) ? $statusClass::UNREAD : 'unread';
            $query = $contactClass::query()->where('status', $status);
            $count = (clone $query)->count();

            $contacts = $query
                ->select(['id', 'name', 'email', 'phone', 'subject', 'created_at'])
                ->latest()
                ->limit(8)
                ->get();
            $canEditContacts = $this->userCan('contacts.edit') && Route::has('contacts.edit');

            return [
                'id' => 'admin-tools-contact-notification',
                'priority' => 30,
                'type' => 'contact',
                'title' => trans('plugins/admin-tools::admin-tools.notifications_contacts_title'),
                'description' => trans('plugins/admin-tools::admin-tools.notifications_contacts_description'),
                'icon' => 'ti ti-mail',
                'color' => 'teal',
                'count' => $count,
                'view_all_url' => route('contacts.index'),
                'view_all_label' => trans('plugins/admin-tools::admin-tools.notifications_view_all'),
                'empty_message' => trans('plugins/admin-tools::admin-tools.notifications_contacts_empty'),
                'items' => $contacts->map(function ($contact) use ($canEditContacts): array {
                    return [
                        'title' => $contact->name,
                        'description' => $contact->subject ?: trim(implode(' - ', array_filter([$contact->phone, $contact->email]))),
                        'url' => $canEditContacts ? route('contacts.edit', $contact->getKey()) : route('contacts.index'),
                        'time' => $contact->created_at,
                        'avatar' => $contact->avatar_url,
                        'meta' => trans('plugins/admin-tools::admin-tools.notifications_contacts_meta'),
                    ];
                })->all(),
            ];
        } catch (Throwable) {
            return null;
        }
    }

    protected function getPaymentNotification(): ?array
    {
        $paymentClass = 'Botble\Payment\Models\Payment';
        $statusClass = 'Botble\Payment\Enums\PaymentStatusEnum';

        if (! $this->isPluginReady('payment', [$paymentClass], ['payment.index'])) {
            return null;
        }

        if (! $this->userCan('payment.index')) {
            return null;
        }

        try {
            $status = class_exists($statusClass) ? $statusClass::PENDING : 'pending';
            $query = $paymentClass::query()->where('status', $status);
            $count = (clone $query)->count();

            $payments = $query
                ->latest()
                ->limit(8)
                ->get();

            return [
                'id' => 'admin-tools-payment-notification',
                'priority' => 40,
                'type' => 'payment',
                'title' => trans('plugins/admin-tools::admin-tools.notifications_payments_title'),
                'description' => trans('plugins/admin-tools::admin-tools.notifications_payments_description'),
                'icon' => 'ti ti-credit-card',
                'color' => 'amber',
                'count' => $count,
                'view_all_url' => route('payment.index'),
                'view_all_label' => trans('plugins/admin-tools::admin-tools.notifications_view_all'),
                'empty_message' => trans('plugins/admin-tools::admin-tools.notifications_payments_empty'),
                'items' => $payments->map(function ($payment): array {
                    return [
                        'title' => $payment->charge_id ?: '#'.$payment->getKey(),
                        'description' => trim($this->getPaymentChannelName($payment).' - '.$this->formatMoney($payment->amount, $payment->currency)),
                        'url' => Route::has('payment.show') ? route('payment.show', $payment->getKey()) : route('payment.index'),
                        'time' => $payment->created_at,
                        'icon' => 'ti ti-credit-card-pay',
                        'meta' => trans('plugins/admin-tools::admin-tools.notifications_payments_meta'),
                    ];
                })->all(),
            ];
        } catch (Throwable) {
            return null;
        }
    }

    protected function notificationItem(array $notification): array
    {
        return [
            'id' => $notification['id'],
            'priority' => $notification['priority'] ?? 100,
            'section' => 'notification',
            'view' => 'plugins/admin-tools::header-left.notification',
            'data' => compact('notification'),
        ];
    }

    protected function normalizeHeaderNotifications(array $notifications): array
    {
        $normalized = [];

        foreach ($notifications as $key => $notification) {
            if (! is_array($notification) || ! $this->canSeeItem($notification)) {
                continue;
            }

            $title = $notification['title'] ?? $notification['label'] ?? $notification['name'] ?? null;

            if (blank($title)) {
                continue;
            }

            $id = $this->normalizeHtmlId(
                $notification['id'] ?? 'admin-tools-header-notification-'.md5((string) $key.$title)
            );
            $viewAllUrl = $notification['view_all_url']
                ?? $notification['url']
                ?? $this->resolveRouteUrl($notification['view_all_route'] ?? $notification['route'] ?? null);

            $normalized[] = [
                'id' => $id,
                'priority' => $notification['priority'] ?? 100,
                'type' => $notification['type'] ?? $id,
                'title' => $title,
                'description' => $notification['description'] ?? $notification['subtitle'] ?? null,
                'icon' => $notification['icon'] ?? 'ti ti-bell',
                'color' => $this->normalizeCssModifier($notification['color'] ?? 'blue'),
                'count' => max((int) ($notification['count'] ?? 0), 0),
                'view_all_url' => $viewAllUrl,
                'view_all_label' => $notification['view_all_label']
                    ?? trans('plugins/admin-tools::admin-tools.notifications_view_all'),
                'empty_message' => $notification['empty_message'] ?? trans('plugins/admin-tools::admin-tools.notifications_empty'),
                'ajax' => is_array($notification['ajax'] ?? null) ? $notification['ajax'] : null,
                'items' => $this->normalizeNotificationItems($notification['items'] ?? [], $viewAllUrl),
            ];
        }

        usort(
            $normalized,
            fn (array $first, array $second): int => ($first['priority'] ?? 100) <=> ($second['priority'] ?? 100)
        );

        return $normalized;
    }

    protected function normalizeNotificationItems(mixed $items, ?string $fallbackUrl = null): array
    {
        if (! is_iterable($items)) {
            return [];
        }

        $normalized = [];

        foreach ($items as $key => $item) {
            if (! is_array($item) || ! $this->canSeeItem($item)) {
                continue;
            }

            $title = $item['title'] ?? $item['label'] ?? $item['name'] ?? null;

            if (blank($title)) {
                continue;
            }

            $url = $item['url'] ?? $this->resolveRouteUrl($item['route'] ?? null) ?? $fallbackUrl ?? '#';

            $normalized[] = [
                'id' => $item['id'] ?? 'admin-tools-header-notification-item-'.md5((string) $key.$title),
                'title' => $title,
                'subtitle' => $item['subtitle'] ?? null,
                'description' => $item['description'] ?? $item['subtitle'] ?? null,
                'url' => $url,
                'time' => $item['time'] ?? null,
                'avatar' => $item['avatar'] ?? null,
                'icon' => $item['icon'] ?? null,
                'meta' => $item['meta'] ?? null,
            ];
        }

        return $normalized;
    }

    protected function normalizeHeaderMenus(array $menus): array
    {
        $normalized = [];

        foreach ($menus as $key => $menu) {
            if (! is_array($menu) || ! $this->canSeeItem($menu)) {
                continue;
            }

            $id = $menu['id'] ?? (is_string($key) ? $key : null);
            $label = $menu['label'] ?? $menu['name'] ?? null;

            if (blank($id) || (blank($label) && blank($menu['icon'] ?? null))) {
                continue;
            }

            $id = $this->normalizeHtmlId($id);
            $items = $menu['items'] ?? $menu['children'] ?? [];
            $menuForFilter = array_merge($menu, [
                'id' => $id,
                'label' => $label,
            ]);
            $items = apply_filters(ADMIN_TOOLS_FILTER_HEADER_MENU_ITEMS, is_array($items) ? $items : [], $id, $menuForFilter);
            $items = $this->normalizeItems(is_array($items) ? $items : []);

            if ($items === []) {
                continue;
            }

            $normalized[] = [
                'id' => $id,
                'label' => $label,
                'icon' => $menu['icon'] ?? null,
                'class' => $menu['class'] ?? null,
                'priority' => $menu['priority'] ?? 100,
                'items' => $items,
            ];
        }

        usort(
            $normalized,
            fn (array $first, array $second): int => ($first['priority'] ?? 100) <=> ($second['priority'] ?? 100)
        );

        return $normalized;
    }

    protected function isPluginReady(string $plugin, array $classes = [], array $routes = []): bool
    {
        if (! function_exists('is_plugin_active') || ! is_plugin_active($plugin)) {
            return false;
        }

        foreach ($classes as $class) {
            if (! class_exists($class)) {
                return false;
            }
        }

        foreach ($routes as $route) {
            if (! Route::has($route)) {
                return false;
            }
        }

        return true;
    }

    protected function userCan(string|array $permissions): bool
    {
        $user = Auth::guard()->user();

        return $user && $user->hasPermission($permissions);
    }

    protected function formatMoney(mixed $amount, ?string $currency = null): string
    {
        if (function_exists('format_price') && $currency === null) {
            return format_price($amount);
        }

        return trim(number_format((float) $amount, 2).' '.($currency ?: ''));
    }

    protected function getPaymentChannelName(mixed $payment): string
    {
        $channel = $payment->payment_channel;

        if (is_object($channel) && method_exists($channel, 'displayName')) {
            return $channel->displayName() ?: trans('plugins/admin-tools::admin-tools.notifications_payments_unknown_method');
        }

        if (is_string($channel) && $channel !== '') {
            return $channel;
        }

        return trans('plugins/admin-tools::admin-tools.notifications_payments_unknown_method');
    }

    protected function routeItem(string $route, string $label, string $icon, string|array|null $permission = null): ?array
    {
        if (! Route::has($route)) {
            return null;
        }

        return [
            'id' => 'admin-tools-fast-menu-'.str_replace('.', '-', $route),
            'label' => $label,
            'url' => route($route),
            'icon' => $icon,
            'permission' => $permission,
        ];
    }

    protected function normalizeItems(array $items): array
    {
        $normalized = [];

        foreach ($items as $key => $item) {
            if (! is_array($item)) {
                continue;
            }

            if (($item['divider'] ?? false) || ($item['type'] ?? null) === 'divider') {
                if ($normalized !== [] && ($normalized[array_key_last($normalized)]['type'] ?? null) !== 'divider') {
                    $normalized[] = ['type' => 'divider'];
                }

                continue;
            }

            if (! $this->canSeeItem($item)) {
                continue;
            }

            $children = $this->normalizeItems($item['children'] ?? []);
            $url = $item['url'] ?? $this->resolveRouteUrl($item['route'] ?? null);

            if ($children === [] && blank($url)) {
                continue;
            }

            $label = $item['label'] ?? $item['name'] ?? null;

            if (blank($label)) {
                continue;
            }

            $normalized[] = [
                'id' => $item['id'] ?? 'admin-tools-fast-menu-'.md5((string) $key.$label),
                'label' => $label,
                'url' => $url ?: '#',
                'icon' => $item['icon'] ?? null,
                'children' => $children,
                'target' => $item['target'] ?? null,
                'rel' => $item['rel'] ?? null,
                'class' => $item['class'] ?? null,
            ];
        }

        $lastKey = array_key_last($normalized);

        if ($lastKey !== null && ($normalized[$lastKey]['type'] ?? null) === 'divider') {
            array_pop($normalized);
        }

        return $normalized;
    }

    protected function normalizeHeaderLeftItems(array $items): array
    {
        $normalized = [];

        foreach ($items as $key => $item) {
            if (is_string($item) || $item instanceof Htmlable) {
                $html = $item instanceof Htmlable ? $item->toHtml() : $item;

                $normalized[] = [
                    'id' => 'admin-tools-header-left-'.md5((string) $key.$html),
                    'type' => 'html',
                    'section' => 'custom',
                    'priority' => 100,
                    'html' => $html,
                ];

                continue;
            }

            if (! is_array($item) || ! $this->canSeeItem($item)) {
                continue;
            }

            $html = $this->renderHeaderLeftHtml($item);

            if (! blank($html)) {
                $normalized[] = [
                    'id' => $item['id'] ?? 'admin-tools-header-left-'.md5((string) $key.$html),
                    'type' => 'html',
                    'section' => $item['section'] ?? 'custom',
                    'priority' => $item['priority'] ?? 100,
                    'html' => $html,
                ];

                continue;
            }

            $children = $this->normalizeItems($item['children'] ?? []);
            $url = $item['url'] ?? $this->resolveRouteUrl($item['route'] ?? null);

            if ($children === [] && blank($url)) {
                continue;
            }

            $label = $item['label'] ?? $item['name'] ?? null;

            if (blank($label) && blank($item['icon'] ?? null)) {
                continue;
            }

            $normalized[] = [
                'id' => $item['id'] ?? 'admin-tools-header-left-'.md5((string) $key.$label),
                'type' => 'item',
                'section' => $item['section'] ?? 'custom',
                'priority' => $item['priority'] ?? 100,
                'label' => $label,
                'url' => $url ?: '#',
                'icon' => $item['icon'] ?? null,
                'children' => $children,
                'target' => $item['target'] ?? null,
                'rel' => $item['rel'] ?? null,
                'class' => $item['class'] ?? null,
            ];
        }

        usort(
            $normalized,
            fn (array $first, array $second): int => $this->getHeaderLeftSectionRank($first['section'] ?? 'custom') <=> $this->getHeaderLeftSectionRank($second['section'] ?? 'custom')
                ?: (($first['priority'] ?? 100) <=> ($second['priority'] ?? 100))
        );

        return $normalized;
    }

    protected function getHeaderLeftSectionRank(string $section): int
    {
        return match ($section) {
            'menu' => 10,
            'cache' => 15,
            'notification' => 20,
            'update' => 30,
            default => 40,
        };
    }

    protected function renderHeaderLeftHtml(array $item): ?string
    {
        $html = $item['html'] ?? null;

        if ($html instanceof Htmlable) {
            return $html->toHtml();
        }

        if (is_string($html)) {
            return $html;
        }

        $view = $item['view'] ?? null;

        if (is_string($view) && view()->exists($view)) {
            return view($view, $item['data'] ?? [])->render();
        }

        return null;
    }

    protected function canSeeItem(array $item): bool
    {
        $permission = $item['permission'] ?? $item['permissions'] ?? null;

        if ($permission === null || $permission === false || $permission === '') {
            return true;
        }

        $user = Auth::guard()->user();

        return $user && $user->hasPermission($permission);
    }

    protected function resolveRouteUrl(mixed $route): ?string
    {
        if (blank($route)) {
            return null;
        }

        if (is_string($route)) {
            return Route::has($route) ? route($route) : null;
        }

        if (! is_array($route)) {
            return null;
        }

        $name = $route['name'] ?? $route[0] ?? null;
        $parameters = $route['parameters'] ?? $route['params'] ?? $route[1] ?? [];

        if (! is_string($name) || ! Route::has($name)) {
            return null;
        }

        return route($name, $parameters);
    }

    protected function normalizeHtmlId(string $id): string
    {
        $originalId = $id;
        $id = trim(preg_replace('/[^A-Za-z0-9\-_:.]+/', '-', $id) ?: '', '-');

        return $id !== '' ? $id : 'admin-tools-header-item-'.md5($originalId);
    }

    protected function normalizeCssModifier(string $value): string
    {
        $value = strtolower(preg_replace('/[^A-Za-z0-9\-_]+/', '', $value) ?: '');

        return $value !== '' ? $value : 'blue';
    }
}
