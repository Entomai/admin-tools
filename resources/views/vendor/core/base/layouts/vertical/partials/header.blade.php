@php
    $adminToolsHideGlobalSearch = function_exists('admin_tools_setting_bool') && admin_tools_setting_bool('hide_global_search', false);
    $adminToolsHideViewWebsite = function_exists('admin_tools_setting_bool') && admin_tools_setting_bool('hide_view_website_button', false);
    $adminToolsCompactBrand = function_exists('admin_tools_setting_bool') && admin_tools_setting_bool('compact_brand_enabled', true);
    $adminToolsHasBrand = ! function_exists('admin_tools_has_admin_brand') || admin_tools_has_admin_brand();
@endphp

<header
    @class([
        'navbar navbar-expand-md d-none d-lg-flex d-print-none',
        'entomai-header-left-navbar' => $adminToolsCompactBrand,
    ])
    data-bs-theme="dark"
>
    <div class="container-fluid">
        <button
            class="navbar-toggler d-none d-lg-block me-2 ms-n1"
            type="button"
            data-bb-toggle="navbar-minimal"
            data-bb-target="#sidebar-menu-main"
            aria-controls="navbar-menu"
            aria-expanded="false"
            aria-label="Toggle navigation"
            @if(Auth::check())
                data-url="{{ route('users.update-preferences', Auth::user()->getKey()) }}"
            @endif
            data-method="PATCH"
        >
            <x-core::icon name="ti ti-menu-2" />
        </button>

        @if ($adminToolsHasBrand)
            <h1 class="navbar-brand navbar-brand-autodark me-4 entomai-admin-brand">
                @include('plugins/admin-tools::partials.admin-brand')
            </h1>
        @endif

        @auth
            {!! apply_filters(BASE_FILTER_TOP_HEADER_LAYOUT, null) !!}
        @endauth

        <div class="flex-row navbar-nav order-md-last ms-auto">
            @unless ($adminToolsHideGlobalSearch)
                <div class="d-flex align-items-center me-3">
                    @include('core/base::global-search.navbar-input')
                </div>
            @endunless

            @if (BaseHelper::getAdminPrefix() != '' && ! $adminToolsHideViewWebsite)
                <div class="d-flex align-items-center me-3">
                    <x-core::button
                        tag="a"
                        :href="url('/')"
                        icon="ti ti-world"
                        target="_blank"
                    >
                        {{ trans('core/base::layouts.view_website') }}
                    </x-core::button>
                </div>
            @endif

            <div class="d-none d-md-flex me-2">
                @include('core/base::layouts.partials.theme-toggle')
            </div>

            @include('core/base::layouts.partials.user-menu')
        </div>

        <div
            class="collapse navbar-collapse"
            id="navbar-menu"
        ></div>
    </div>
</header>
