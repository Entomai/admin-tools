<div
    class="nav-item dropdown entomai-header-notification entomai-header-notification--cyan entomai-fast-cache-cleaner"
    data-entomai-fast-cache-cleaner
>
    <button
        class="nav-link px-0 entomai-header-notification__trigger"
        data-bs-toggle="dropdown"
        data-bs-auto-close="outside"
        type="button"
        aria-label="{{ trans('plugins/admin-tools::admin-tools.fast_cache_title') }}"
    >
        <x-core::icon name="ti ti-refresh" />
    </button>

    <div class="dropdown-menu dropdown-menu-arrow dropdown-menu-card entomai-header-notification__menu entomai-fast-cache-cleaner__menu">
        <div class="card entomai-header-notification__card">
            <div class="card-header entomai-header-notification__header">
                <div class="entomai-header-notification__header-icon">
                    <x-core::icon name="ti ti-refresh" />
                </div>
                <div class="entomai-header-notification__heading">
                    <h3 class="card-title mb-0">{{ trans('plugins/admin-tools::admin-tools.fast_cache_title') }}</h3>
                    <div class="text-secondary small">
                        {{ trans('plugins/admin-tools::admin-tools.fast_cache_description') }}
                    </div>
                </div>
                @if (Route::has('system.cache'))
                    <div class="card-actions">
                        <a href="{{ route('system.cache') }}">{{ trans('plugins/admin-tools::admin-tools.fast_cache_manage') }}</a>
                    </div>
                @endif
            </div>

            <div class="list-group list-group-flush entomai-fast-cache-cleaner__list">
                @foreach ($commands as $command)
                    <div class="list-group-item entomai-fast-cache-cleaner__item">
                        <div class="row g-2 align-items-center">
                            <div class="col-auto">
                                <span class="avatar avatar-sm bg-{{ $command['color'] }}-lt">
                                    <x-core::icon :name="$command['icon']" />
                                </span>
                            </div>
                            <div class="col text-truncate">
                                <strong class="d-block text-truncate">{{ $command['title'] }}</strong>
                                <div class="text-secondary text-truncate">{{ $command['description'] }}</div>
                                @if ($command['show_size'])
                                    <div class="mt-1">
                                        <span class="status status-primary">
                                            <span class="status-dot status-dot-animated"></span>
                                            <strong>{{ trans('core/base::cache.current_size') }}:</strong>
                                            <span data-entomai-cache-size>{{ $formattedCacheSize }}</span>
                                        </span>
                                    </div>
                                @endif
                            </div>
                            <div class="col-auto">
                                <x-core::button
                                    type="button"
                                    size="sm"
                                    :color="$command['color']"
                                    :icon="$command['button_icon']"
                                    data-entomai-cache-action
                                    data-type="{{ $command['type'] }}"
                                    data-url="{{ route('admin-tools.cache.clear') }}"
                                >
                                    {{ $command['button_label'] }}
                                </x-core::button>
                            </div>
                        </div>
                    </div>
                @endforeach
            </div>

            <div class="entomai-fast-cache-cleaner__footer">
                <x-core::button
                    type="button"
                    color="primary"
                    icon="ti ti-trash"
                    class="w-100"
                    data-entomai-cache-action
                    data-type="clear_all"
                    data-url="{{ route('admin-tools.cache.clear') }}"
                >
                    {{ trans('plugins/admin-tools::admin-tools.fast_cache_clear_all') }}
                </x-core::button>
            </div>
        </div>
    </div>
</div>
