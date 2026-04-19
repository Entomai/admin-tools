<div
    class="entomai-header-left d-none"
    data-entomai-header-left
    data-entomai-sticky-shell="{{ ($settings['sticky_header_enabled'] ?? true) ? '1' : '0' }}"
    data-entomai-compact-brand="{{ ($settings['compact_brand_enabled'] ?? true) ? '1' : '0' }}"
    data-entomai-hide-view-website="{{ ($settings['hide_view_website_button'] ?? false) ? '1' : '0' }}"
>
    @if ($fastMenuItems !== [])
        @include('plugins/admin-tools::fast-menu.index', ['items' => $fastMenuItems])
    @endif

    @foreach ($headerLeftItems as $headerLeftItem)
        @include('plugins/admin-tools::header-left.item', ['item' => $headerLeftItem])
    @endforeach
</div>
