<?php

namespace Botble\AdminTools\Http\Requests\Settings;

use Botble\Support\Http\Requests\Request;

class AdminToolsSettingRequest extends Request
{
    public function rules(): array
    {
        return [
            'admin_tools_fast_menu_enabled' => ['nullable', 'boolean'],
            'admin_tools_fast_cache_cleaner_enabled' => ['nullable', 'boolean'],
            'admin_tools_ecommerce_header_menu_enabled' => ['nullable', 'boolean'],
            'admin_tools_ecommerce_notifications_enabled' => ['nullable', 'boolean'],
            'admin_tools_contact_notifications_enabled' => ['nullable', 'boolean'],
            'admin_tools_payment_notifications_enabled' => ['nullable', 'boolean'],
            'admin_tools_ecommerce_notification_statuses' => ['nullable', 'array'],
            'admin_tools_ecommerce_notification_statuses.*' => ['string', 'max:80'],
            'admin_tools_contact_notification_statuses' => ['nullable', 'array'],
            'admin_tools_contact_notification_statuses.*' => ['string', 'max:80'],
            'admin_tools_payment_notification_statuses' => ['nullable', 'array'],
            'admin_tools_payment_notification_statuses.*' => ['string', 'max:80'],
            'admin_tools_hotel_booking_notification_statuses' => ['nullable', 'array'],
            'admin_tools_hotel_booking_notification_statuses.*' => ['string', 'max:80'],
            'admin_tools_update_header_widget_enabled' => ['nullable', 'boolean'],
            'admin_tools_sticky_header_enabled' => ['nullable', 'boolean'],
            'admin_tools_compact_brand_enabled' => ['nullable', 'boolean'],
            'admin_tools_remove_logo_enabled' => ['nullable', 'boolean'],
            'admin_tools_brand_custom_text' => ['nullable', 'string', 'max:120'],
            'admin_tools_hide_view_website_button' => ['nullable', 'boolean'],
            'admin_tools_hide_global_search' => ['nullable', 'boolean'],
            'admin_tools_hide_botble_notification' => ['nullable', 'boolean'],
            'admin_tools_admin_appearance_per_user_enabled' => ['nullable', 'boolean'],
            'admin_tools_hidden_header_hook_items' => ['nullable', 'array'],
            'admin_tools_hidden_header_hook_items.*' => ['string', 'max:160'],
        ];
    }
}
