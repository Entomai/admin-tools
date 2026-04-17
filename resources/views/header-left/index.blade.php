<div
    class="entomai-header-left d-none"
    data-entomai-header-left
>
    @if ($fastMenuItems !== [])
        @include('plugins/admin-tools::fast-menu.index', ['items' => $fastMenuItems])
    @endif

    @foreach ($headerLeftItems as $headerLeftItem)
        @include('plugins/admin-tools::header-left.item', ['item' => $headerLeftItem])
    @endforeach
</div>
