@if (!$hideMenuSearch)
    <div class="menu-search">
        @partial('@searchbar')
    </div>
@endif

<div class="menu-list">
    @if ($menuIsGrouped)
        @partial('@grouped', ['groupedMenuItems' => $menuList])
    @else
        @partial('@items', ['menuItems' => $menuList])
    @endif

    <div class="pagination-bar text-right">
        <div class="links">{!! $menuList->links() !!}</div>
    </div>
</div>
