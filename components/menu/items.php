<div class="menu-items">
    <?php if (count($menuItems)) { ?>
        <?php foreach ($menuItems as $menuItem) { ?>
            <?= partial('@item', ['menuItem' => $menuItem]); ?>
        <?php } ?>
    <?php }
    else { ?>
        <p><?= lang('sampoyigi.local::default.local.text_empty'); ?></p>
    <?php } ?>
</div>
