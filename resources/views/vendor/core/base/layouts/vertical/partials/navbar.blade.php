@php
    $adminToolsHideGlobalSearch = function_exists('admin_tools_setting_bool') && admin_tools_setting_bool('hide_global_search', false);
    $adminToolsHideViewWebsite = function_exists('admin_tools_setting_bool') && admin_tools_setting_bool('hide_view_website_button', false);
    $adminToolsCompactBrand = function_exists('admin_tools_setting_bool') && admin_tools_setting_bool('compact_brand_enabled', true);
@endphp

@include('core/base::layouts.' . AdminAppearance::getCurrentLayout() . '.partials.aside')

<header
    @class([
        'navbar navbar-expand-md d-none d-lg-flex d-print-none',
        'entomai-header-left-navbar' => $adminToolsCompactBrand,
    ])
    data-bs-theme="dark"
>
    <div class="{{ AdminAppearance::getContainerWidth() }}">
        <button
            class="navbar-toggler"
            type="button"
            data-bs-toggle="collapse"
            data-bs-target="#navbar-menu"
            aria-controls="navbar-menu"
            aria-expanded="false"
            aria-label="Toggle navigation"
        >
            <span class="navbar-toggler-icon"></span>
        </button>

        @unless ($adminToolsHideGlobalSearch)
            <div class="flex-row navbar-nav">
                <div class="d-flex align-items-center me-3">
                    @include('core/base::global-search.navbar-input')
                </div>
            </div>
        @endunless

        @auth
            {!! apply_filters(BASE_FILTER_TOP_HEADER_LAYOUT, null) !!}
        @endauth

        <div class="flex-row navbar-nav order-md-last ms-auto">
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
