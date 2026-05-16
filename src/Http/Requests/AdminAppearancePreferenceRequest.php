<?php

namespace Botble\AdminTools\Http\Requests;

use Botble\Base\Facades\AdminAppearance;
use Botble\Base\Facades\AdminHelper;
use Botble\Base\Rules\ColorRule;
use Botble\Support\Http\Requests\Request;
use Illuminate\Validation\Rule;

class AdminAppearancePreferenceRequest extends Request
{
    public function rules(): array
    {
        return [
            'admin_tools_admin_appearance_locale' => ['sometimes', Rule::in(array_keys(AdminHelper::getAdminLocales()))],
            'admin_tools_admin_appearance_locale_direction' => ['required', 'string', 'in:ltr,rtl'],
            'admin_tools_admin_appearance_layout' => ['required', 'string', Rule::in(array_keys(AdminAppearance::getLayouts()))],
            'admin_tools_admin_appearance_container_width' => ['required', 'string', Rule::in(array_keys(AdminAppearance::getContainerWidths()))],
            'admin_tools_admin_appearance_show_menu_item_icon' => ['nullable', 'boolean'],
            'admin_tools_admin_appearance_theme_mode' => ['required', 'string', 'in:light,dark'],
            'admin_tools_admin_appearance_primary_color' => ['nullable', new ColorRule],
            'admin_tools_admin_appearance_secondary_color' => ['nullable', new ColorRule],
            'admin_tools_admin_appearance_heading_color' => ['nullable', 'string', 'regex:/^(inherit|#(?:[0-9a-fA-F]{3}){1,2})$/'],
            'admin_tools_admin_appearance_text_color' => ['nullable', new ColorRule],
            'admin_tools_admin_appearance_link_color' => ['nullable', new ColorRule],
            'admin_tools_admin_appearance_link_hover_color' => ['nullable', new ColorRule],
        ];
    }
}
