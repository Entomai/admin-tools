@php
    $menu ??= [
        'id' => 'admin-tools-fast-menu',
        'label' => trans('plugins/admin-tools::admin-tools.fast_menu'),
        'icon' => 'ti ti-bolt',
        'class' => null,
    ];

    $menuId = $menu['id'];
    $menuLabel = $menu['label'];
    $menuIcon = $menu['icon'] ?? null;
@endphp

<div
    @class(['nav-item dropdown entomai-fast-menu', $menu['class'] ?? null])
    data-entomai-fast-menu
    data-entomai-header-menu="{{ $menuId }}"
>
    <a
        class="nav-link px-2 dropdown-toggle"
        href="#{{ $menuId }}"
        id="{{ $menuId }}"
        data-bs-toggle="dropdown"
        data-bs-auto-close="outside"
        role="button"
        aria-expanded="false"
        aria-label="{{ $menuLabel ?: $menuId }}"
    >
        @if ($menuIcon)
            <x-core::icon :name="$menuIcon" />
        @endif

        @if (! blank($menuLabel))
            <span class="nav-link-title">{{ $menuLabel }}</span>
        @endif
    </a>

    <div
        class="dropdown-menu animate slideIn"
        aria-labelledby="{{ $menuId }}"
    >
        @foreach ($items as $item)
            @include('plugins/admin-tools::fast-menu.item', ['item' => $item, 'level' => 1])
        @endforeach
    </div>
</div>
