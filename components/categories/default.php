<div class="panel panel-default">
    <div class="list-group list-group-root">
        <?php if (strlen($selectedCategory)) { ?>
            <a
                class="list-group-item text-danger"
                href="<?= page_url('local/menus', ['category' => null]) ?>"
            >
                <i class="fa fa-times"></i>&nbsp;&nbsp;<?= lang('sampoyigi.local::default.text_clear'); ?>
            </a>
        <?php } ?>

        <?= partial('@items', ['categories' => $categories]); ?>
    </div>
</div>