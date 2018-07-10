<div class="menu-list">
    <?= partial('@items', ['menuItems' => $menuList]); ?>

    <div class="pagination-bar text-right">
        <div class="links"><?= $menuList->links(); ?></div>
    </div>
</div>
