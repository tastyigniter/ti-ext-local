<?php
?>
<div class="menu-list">
    <?php if ($groupMenuList) { ?>
        <?= partial('@grouped'); ?>
    <?php } else { ?>
        <?= partial('@items', ['menuItems' => $menuList]); ?>
    <?php } ?>
</div>
