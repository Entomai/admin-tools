@php
    $level ??= 0;

    if (($item['type'] ?? null) === 'divider') {
        $isDivider = true;
    } else {
        $isDivider = false;
        $children = $item['children'] ?? [];
        $hasChildren = $children !== [];
        $target = $item['target'] ?? null;
        $rel = $item['rel'] ?? ($target === '_blank' ? 'noopener noreferrer' : null);
    }
@endphp

@if ($isDivider)
    @if ($level > 0)
        <div class="dropdown-divider"></div>
    @endif
@elseif ($level === 0)
    <li @class(['nav-item', 'dropdown' => $hasChildren, $item['class'] ?? null])>
        <a
            @class(['nav-link px-2', 'dropdown-toggle' => $hasChildren])
            href="{{ $hasChildren ? '#' . $item['id'] : $item['url'] }}"
            @if ($hasChildren)
                id="{{ $item['id'] }}"
                data-bs-toggle="dropdown"
                data-bs-auto-close="outside"
                role="button"
                aria-expanded="false"
            @endif
            @if ($target) target="{{ $target }}" @endif
            @if ($rel) rel="{{ $rel }}" @endif
        >
            @if ($item['icon'])
                <x-core::icon :name="$item['icon']" />
            @endif

            <span class="nav-link-title">{{ $item['label'] }}</span>
        </a>

        @if ($hasChildren)
            <div
                class="dropdown-menu animate slideIn"
                aria-labelledby="{{ $item['id'] }}"
            >
                @foreach ($children as $child)
                    @include('plugins/admin-tools::fast-menu.item', ['item' => $child, 'level' => $level + 1])
                @endforeach
            </div>
        @endif
    </li>
@elseif ($hasChildren)
    <div @class(['dropend', $item['class'] ?? null])>
        <a
            class="dropdown-item dropdown-toggle"
            href="#{{ $item['id'] }}"
            data-entomai-fast-menu-submenu
            role="button"
            aria-expanded="false"
        >
            @if ($item['icon'])
                <x-core::icon :name="$item['icon']" />
            @endif

            <span>{{ $item['label'] }}</span>
        </a>

        <div class="dropdown-menu animate slideIn">
            @foreach ($children as $child)
                @include('plugins/admin-tools::fast-menu.item', ['item' => $child, 'level' => $level + 1])
            @endforeach
        </div>
    </div>
@else
    <a
        @class(['dropdown-item', $item['class'] ?? null])
        href="{{ $item['url'] }}"
        @if ($target) target="{{ $target }}" @endif
        @if ($rel) rel="{{ $rel }}" @endif
    >
        @if ($item['icon'])
            <x-core::icon :name="$item['icon']" />
        @endif

        <span>{{ $item['label'] }}</span>
    </a>
@endif
