<div class="list-group">
    <?php if (!count($menuList)) { ?>
        <div class="list-group-item">
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

            <div class="list-group-item">
                <div id="<?= $menuCategoryAlias; ?>-heading" role="tab">
                    <h4
                        class="category-title"
                        role="button"
                        data-toggle="collapse"
                        href="#<?= $menuCategoryAlias; ?>-collapse"
                        aria-expanded="false"
                        aria-controls="<?= $menuCategoryAlias; ?>-heading"
                    >
                        <?= $menuCategory->name; ?>
                        <i class="fa fa-pull-right"></i>
                    </h4>
                </div>

                <div
                    id="<?= $menuCategoryAlias; ?>-collapse"
                    class="collapse in"
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