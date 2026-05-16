<div
    class="entomai-header-left d-flex align-items-center gap-2 me-3"
    data-entomai-header-left
    data-entomai-header-inline="1"
    data-entomai-sticky-shell="{{ ($settings['sticky_header_enabled'] ?? true) ? '1' : '0' }}"
    data-entomai-compact-brand="{{ ($settings['compact_brand_enabled'] ?? true) ? '1' : '0' }}"
>
    @if ($fastMenuItems !== [])
        @include('plugins/admin-tools::fast-menu.index', ['items' => $fastMenuItems])
    @endif

    @foreach ($headerLeftItems as $headerLeftItem)
        @include('plugins/admin-tools::header-left.item', ['item' => $headerLeftItem])
    @endforeach
</div>
