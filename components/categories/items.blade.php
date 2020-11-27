@foreach ($categories as $category)
    @continue(in_array($category->getKey(), $hiddenCategories))
    @continue($hideEmptyCategory AND $category->count_menus < 1)

    <a
        class="nav-link{{ ($selectedCategory AND $category->permalink_slug == $selectedCategory->permalink_slug) ? ' active' : '' }}"
        href="{{ page_url('local/menus', ['category' => $category->permalink_slug]) }}"
    >{{ $category->name }}</a>

    @if (count($category->children))
        <nav class="nav nav-categories flex-column ml-3 my-1">
            @partial('@items', ['categories' => $category->children])
        </nav>
    @endif
@endforeach
