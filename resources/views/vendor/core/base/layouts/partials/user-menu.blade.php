<x-core::dropdown
    wrapper-class="nav-item"
    :has-arrow="true"
    position="end"
>
    <x-slot:trigger>
        <a
            href="{{ Auth::guard()->user()->url }}"
            class="p-0 nav-link d-flex lh-1 text-reset"
            data-bs-toggle="dropdown"
            aria-label="{{ trans('core/base::forms.open_user_menu') }}"
        >
            <span
                class="crop-image-original avatar avatar-sm"
                style="background-image: url({{ Auth::guard()->user()->avatar_url }})"
            ></span>
            <div class="d-none d-xl-block ps-2">
                <div>{{ Auth::guard()->user()->name }}</div>
                <div class="mt-1 small text-muted">{{ Auth::guard()->user()->email }}</div>
            </div>
        </a>
    </x-slot:trigger>

    <x-core::dropdown.item
        :href="Auth::guard()->user()->url"
        :label="trans('core/base::layouts.profile')"
        icon="ti ti-user"
    />

    @if (function_exists('admin_tools_setting_bool') && admin_tools_setting_bool('admin_appearance_per_user_enabled', false) && Route::has('admin-tools.admin-appearance.edit'))
        <x-core::dropdown.item
            :href="route('admin-tools.admin-appearance.edit')"
            :label="trans('plugins/admin-tools::admin-tools.user_menu_settings')"
            icon="ti ti-settings"
        />
    @endif

    <x-core::dropdown.item
        :href="route('access.logout')"
        :label="trans('core/base::layouts.logout')"
        icon="ti ti-logout"
    />
</x-core::dropdown>
