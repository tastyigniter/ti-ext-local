<div class="d-block d-sm-none">
    <button
        class="btn btn-light btn-block px-3 text-left"
        data-toggle="collapse"
        data-target="#collapseCategories{{ $id = uniqid('collapse') }}"
        aria-expanded="false"
        aria-controls="collapseCategories"
    ><i class="fa fa-bars"></i>&nbsp;&nbsp;
        @if ($selectedCategory)
            {{ $selectedCategory->name }}
        @else
            @lang('igniter.local::default.text_categories')
        @endif
    </button>
</div>
<div
    id="collapseCategories{{ $id }}"
    class="collapse d-sm-block"
>
    <h2 class="h5 px-3 d-none d-sm-block">@lang('igniter.local::default.text_categories')</h2>
    <nav class="nav nav-categories flex-column">
        @if ($selectedCategory)
            <a
                class="nav-link text-danger"
                href="{{ page_url('local/menus', ['category' => null]) }}"
            >
                <i class="fa fa-times"></i>&nbsp;&nbsp;@lang('igniter.local::default.text_clear')
            </a>
        @endif

        @partial('@items', ['categories' => $categories->toTree()])
    </nav>
</div>
