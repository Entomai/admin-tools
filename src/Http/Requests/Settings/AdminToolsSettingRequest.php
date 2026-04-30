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
            'admin_tools_update_header_widget_enabled' => ['nullable', 'boolean'],
            'admin_tools_sticky_header_enabled' => ['nullable', 'boolean'],
            'admin_tools_compact_brand_enabled' => ['nullable', 'boolean'],
            'admin_tools_hide_view_website_button' => ['nullable', 'boolean'],
        ];
    }
}
