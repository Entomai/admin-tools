@php
    $adminToolsShowBrandLogo = ! function_exists('admin_tools_should_show_brand_logo') || admin_tools_should_show_brand_logo();
    $adminToolsBrandCustomText = function_exists('admin_tools_brand_custom_text') ? admin_tools_brand_custom_text() : '';
    $adminToolsAdminLogo = setting('admin_logo');
    $adminToolsDefaultLogo = config('core.base.general.logo');
    $adminToolsAdminTitle = setting('admin_title', config('core.base.general.base_name'));
@endphp

@if ($adminToolsShowBrandLogo || $adminToolsBrandCustomText !== '')
    <a
        href="{{ route('dashboard.index') }}"
        class="entomai-admin-brand-link"
    >
        @if ($adminToolsShowBrandLogo)
            <img
                src="{{ $adminToolsAdminLogo ? RvMedia::getImageUrl($adminToolsAdminLogo) : url($adminToolsDefaultLogo) }}"
                style="max-height: {{ setting('admin_logo_max_height', $defaultLogoHeight ?? 32) }}px; height: auto;"
                alt="{{ $adminToolsBrandCustomText ?: $adminToolsAdminTitle }}"
                class="navbar-brand-image"
            >
        @endif

        @if ($adminToolsBrandCustomText !== '')
            <span class="entomai-admin-brand-text">{{ $adminToolsBrandCustomText }}</span>
        @endif
    </a>
@endif
