<div class="list-group list-group-flush">
    <?php if (!count($menuList)) { ?>
        <div class="menu-group-item">
            <p><?= lang('sampoyigi.local::default.text_no_category'); ?></p>
        </div>
    <?php }
    else { ?>
        <?php $index = 0;
        foreach ($menuList as $category_id => $menuCategory) { ?>
            <?php
            $index++;
            $menuCategoryAlias = strtolower(str_slug($menuCategory->name));
            ?>

            <div class="menu-group-item">
                <div id="<?= $menuCategoryAlias; ?>-heading" role="tab">
                    <h5
                        class="category-title"
                        data-toggle="collapse"
                        data-target="#<?= $menuCategoryAlias; ?>-collapse"
                        aria-expanded="false"
                        aria-controls="<?= $menuCategoryAlias; ?>-heading"
                    >
                        <?= $menuCategory->name; ?>
                    </h5>
                </div>

                <div
                    id="<?= $menuCategoryAlias; ?>-collapse"
                    class="collapse <?= $index < 5 ? 'show' : ''; ?>"
                    role="tabpanel" aria-labelledby="<?= $menuCategoryAlias; ?>"
                >
                    <div class="menu-category">
                        <?php if (strlen($menuCategory->description)) { ?>
                            <p><?= $menuCategory->description; ?></p>
                        <?php } ?>

                        <?php if (strlen($menuCategory->image)) { ?>
                            <div class="image">
                                <img
                                    class="img-responsive"
                                    src="<?= $menuCategory->getThumb(); ?>"
                                    alt="<?= $menuCategory->name; ?>"/>
                            </div>
                        <?php } ?>
                    </div>

                    <?= partial('@items', ['menuItems' => $menuCategory->menus]); ?>
                </div>
            </div>
        <?php } ?>
    <?php } ?>
</div>