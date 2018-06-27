<?php foreach ($categories as $category) { ?>
    <?php
    $isActive = ($selectedCategory AND $category->permalink_slug == $selectedCategory->permalink_slug);
    $children = $category->children;
    ?>
    <a
        class="nav-link<?= $isActive ? ' active' : ''; ?>"
        href="<?= page_url('local/menus', ['category' => $category->permalink_slug]) ?>"
    ><?= $category->name ?></a>

    <?php if (count($children)) { ?>
        <nav class="nav nav-categories flex-column ml-3 my-1">
            <?= partial('@items', ['categories' => $children]); ?>
        </nav>
    <?php } ?>
<?php } ?>