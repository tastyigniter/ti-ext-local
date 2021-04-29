@foreach ($categories as $category)
    @continue(in_array($category->getKey(), $hiddenCategories))
    @continue($hideEmptyCategory AND $category->count_menus < 1)

    <li class="nav-item">
        <a
            class="nav-link font-weight-bold{{ ($selectedCategory AND $category->permalink_slug == $selectedCategory->permalink_slug) ? ' active' : '' }}"
            href="{{ page_url('local/menus', ['category' => $category->permalink_slug]) }}"
        >{{ $category->name }}</a>

        @if ((!isset($displayAsFlatTree) OR !$displayAsFlatTree) AND count($category->children))
            <ul class="nav flex-column ml-3 my-1">
                @partial('@items', ['categories' => $category->children])
            </ul>
        @endif
    </li>
@endforeach
