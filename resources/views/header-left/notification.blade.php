<div
    class="nav-item dropdown entomai-header-notification entomai-header-notification--{{ $notification['color'] }}"
    data-entomai-notification-id="{{ $notification['id'] }}"
    data-entomai-notification="{{ $notification['type'] }}"
    @if (! empty($notification['ajax']['url'])) data-entomai-notification-ajax-url="{{ $notification['ajax']['url'] }}" @endif
    @if (! empty($notification['ajax']['interval'])) data-entomai-notification-ajax-interval="{{ $notification['ajax']['interval'] }}" @endif
>
    <button
        class="nav-link px-0 entomai-header-notification__trigger"
        data-bs-toggle="dropdown"
        data-bs-auto-close="outside"
        type="button"
        aria-label="{{ $notification['title'] }}"
    >
        <x-core::icon :name="$notification['icon']" />
        <span class="entomai-header-notification__badge">{{ number_format($notification['count']) }}</span>
    </button>

    <div class="dropdown-menu dropdown-menu-arrow dropdown-menu-card entomai-header-notification__menu">
        <div class="card entomai-header-notification__card">
            <div class="card-header entomai-header-notification__header">
                <div class="entomai-header-notification__header-icon">
                    <x-core::icon :name="$notification['icon']" />
                </div>
                <div class="entomai-header-notification__heading">
                    <h3 class="card-title mb-0">{{ $notification['title'] }}</h3>
                    @if (! blank($notification['description'] ?? null))
                        <div class="text-secondary small">{{ $notification['description'] }}</div>
                    @endif
                </div>
                @if (! blank($notification['view_all_url'] ?? null))
                    <div class="card-actions">
                        <a href="{{ $notification['view_all_url'] }}">{{ $notification['view_all_label'] }}</a>
                    </div>
                @endif
            </div>

            @if (! empty($notification['items']))
                <div class="list-group list-group-flush list-group-hoverable entomai-header-notification__list">
                    @foreach ($notification['items'] as $item)
                        <a
                            href="{{ $item['url'] }}"
                            class="list-group-item list-group-item-action entomai-header-notification__item"
                        >
                            <div class="row g-2 align-items-center">
                                <div class="col-auto">
                                    @if (! empty($item['avatar']))
                                        <span
                                            class="avatar avatar-sm"
                                            style="background-image: url('{{ $item['avatar'] }}')"
                                        ></span>
                                    @else
                                        <span class="avatar avatar-sm entomai-header-notification__item-icon">
                                            <x-core::icon :name="$item['icon'] ?? $notification['icon']" />
                                        </span>
                                    @endif
                                </div>
                                <div class="col text-truncate">
                                    <div class="d-flex align-items-center gap-2">
                                        <strong class="text-truncate">{{ $item['title'] }}</strong>
                                        @if (! empty($item['time']))
                                            <time
                                                class="small text-secondary ms-auto text-nowrap"
                                                title="{{ $formattedTime = BaseHelper::formatDateTime($item['time']) }}"
                                                datetime="{{ $formattedTime }}"
                                            >
                                                {{ $item['time']->diffForHumans() }}
                                            </time>
                                        @endif
                                    </div>
                                    @if (! blank($item['description'] ?? null))
                                        <div class="text-secondary text-truncate">{{ $item['description'] }}</div>
                                    @endif
                                    @if (! empty($item['meta']))
                                        <div class="small text-secondary">{{ $item['meta'] }}</div>
                                    @endif
                                </div>
                            </div>
                        </a>
                    @endforeach
                </div>
            @else
                <div class="entomai-header-notification__empty">
                    <div class="entomai-header-notification__empty-icon">
                        <x-core::icon :name="$notification['icon']" />
                    </div>
                    <div>{{ $notification['empty_message'] }}</div>
                </div>
            @endif
        </div>
    </div>
</div>
