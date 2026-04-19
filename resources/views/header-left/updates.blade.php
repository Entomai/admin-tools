@php
    $updates = collect($updates ?? []);
    $counts = $state['counts'] ?? ['plugins' => 0, 'themes' => 0, 'total' => $updates->count()];
@endphp

<div
    class="nav-item dropdown entomai-header-notification entomai-header-notification--green entomai-header-updates"
    data-entomai-header-updates
>
    <button
        class="nav-link px-0 entomai-header-notification__trigger"
        data-bs-toggle="dropdown"
        data-bs-auto-close="outside"
        type="button"
        aria-label="{{ trans('plugins/admin-tools::admin-tools.header_updates_title') }}"
    >
        <x-core::icon name="ti ti-cloud-download" />
        <span class="entomai-header-notification__badge">{{ number_format($counts['total'] ?? $updates->count()) }}</span>
    </button>

    <div class="dropdown-menu dropdown-menu-arrow dropdown-menu-card entomai-header-notification__menu entomai-header-updates__menu">
        <form method="POST" action="{{ route('admin-tools.updates.update') }}">
            @csrf

            <div class="card entomai-header-notification__card">
                <div class="card-header entomai-header-notification__header">
                    <div class="entomai-header-notification__header-icon">
                        <x-core::icon name="ti ti-cloud-download" />
                    </div>
                    <div class="entomai-header-notification__heading">
                        <h3 class="card-title mb-0">{{ trans('plugins/admin-tools::admin-tools.header_updates_title') }}</h3>
                        <div class="text-secondary small">
                            {{ trans('plugins/admin-tools::admin-tools.header_updates_description', [
                                'plugins' => $counts['plugins'] ?? 0,
                                'themes' => $counts['themes'] ?? 0,
                            ]) }}
                        </div>
                    </div>
                </div>

                @foreach ($messages ?? [] as $message)
                    <div class="alert alert-warning rounded-0 border-0 border-bottom mb-0 py-2 small">
                        {{ $message }}
                    </div>
                @endforeach

                @if ($updates->isNotEmpty())
                    <div class="list-group list-group-flush entomai-header-updates__list">
                        @foreach ($updates as $item)
                            <label class="list-group-item entomai-header-updates__item">
                                <div class="row g-2 align-items-center">
                                    <div class="col-auto">
                                        <input
                                            class="form-check-input"
                                            type="checkbox"
                                            name="updates[]"
                                            value="{{ $item['key'] }}"
                                            aria-label="{{ trans('plugins/admin-tools::admin-tools.select_update', ['item' => $item['name']]) }}"
                                        >
                                    </div>
                                    <div class="col-auto">
                                        <span class="avatar avatar-sm entomai-header-notification__item-icon">
                                            <x-core::icon :name="$item['icon']" />
                                        </span>
                                    </div>
                                    <div class="col text-truncate">
                                        <div class="d-flex align-items-center gap-2">
                                            <strong class="text-truncate">{{ $item['name'] }}</strong>
                                            <span class="badge bg-secondary-lt text-secondary ms-auto">{{ $item['type_label'] }}</span>
                                        </div>
                                        <div class="text-secondary text-truncate">
                                            {{ trans('plugins/admin-tools::admin-tools.header_updates_version_line', [
                                                'current' => $item['current_version'] ?: '-',
                                                'latest' => $item['latest_version'] ?: '-',
                                            ]) }}
                                        </div>
                                        <div class="small text-secondary text-truncate">{{ $item['source_label'] }}</div>
                                    </div>
                                </div>
                            </label>
                        @endforeach
                    </div>

                    <div class="entomai-header-updates__footer">
                        <x-core::button
                            type="submit"
                            color="primary"
                            icon="ti ti-download"
                            class="w-100"
                        >
                            {{ trans('plugins/admin-tools::admin-tools.update_selected') }}
                        </x-core::button>
                    </div>
                @else
                    <div class="entomai-header-notification__empty">
                        <div class="entomai-header-notification__empty-icon">
                            <x-core::icon name="ti ti-circle-check" />
                        </div>
                        <div>{{ trans('plugins/admin-tools::admin-tools.header_updates_empty') }}</div>
                    </div>
                @endif
            </div>
        </form>
    </div>
</div>
