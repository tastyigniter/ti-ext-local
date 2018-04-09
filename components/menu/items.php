<div class="menu-items">
    <?php if (count($menuItems)) { ?>
        <?php foreach ($menuItems as $menuItem) { ?>
            <?= partial('@item', ['menuItem' => $menuItem]); ?>
        <?php } ?>
    <?php }
    else { ?>
        <p><?= lang('sampoyigi.local::default.text_empty'); ?></p>
    <?php } ?>
</div>
