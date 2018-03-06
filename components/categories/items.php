<?php foreach ($categories as $category) { ?>
    <?php
    $isActive = ($category->permalink_slug == $selectedCategory);
    $children = $category->children;
    ?>
    <a
        class="list-group-item<?= $isActive ? ' active' : ''; ?>"
        href="<?= page_url('local/menus', ['category' => $category->permalink_slug]) ?>"
    >
        <?= $category->name ?>
        <?php if (count($children)) { ?>
            <i class="fa fa-plus-square fa-pull-right" data-toggle="collapse"></i>
        <?php } ?>
    </a>

    <?php if (count($children)) { ?>
        <div class="list-group collapse<?= $category->isRoot() ? ' in' : '' ?>">
            <?= partial('@items', ['categories' => $children]); ?>
        </div>
    <?php } ?>
<?php } ?>