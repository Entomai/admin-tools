@if (($item['type'] ?? null) === 'html')
    {!! $item['html'] !!}
@else
    @php
        $children = $item['children'] ?? [];
        $hasChildren = $children !== [];
        $target = $item['target'] ?? null;
        $rel = $item['rel'] ?? ($target === '_blank' ? 'noopener noreferrer' : null);
    @endphp

    <div @class(['nav-item d-flex align-items-center', 'dropdown' => $hasChildren, $item['class'] ?? null])>
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
            @if (blank($item['label'])) aria-label="{{ $item['id'] }}" @endif
            @if ($target) target="{{ $target }}" @endif
            @if ($rel) rel="{{ $rel }}" @endif
        >
            @if ($item['icon'])
                <x-core::icon :name="$item['icon']" />
            @endif

            @if ($item['label'])
                <span class="nav-link-title">{{ $item['label'] }}</span>
            @endif
        </a>

        @if ($hasChildren)
            <div
                class="dropdown-menu animate slideIn"
                aria-labelledby="{{ $item['id'] }}"
            >
                @foreach ($children as $child)
                    @include('plugins/admin-tools::fast-menu.item', ['item' => $child, 'level' => 1])
                @endforeach
            </div>
        @endif
    </div>
@endif
