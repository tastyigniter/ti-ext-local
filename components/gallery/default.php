<?php if (isset($gallery['images']) AND count($gallery['images'])) { ?>
    <h5><b><?= $gallery['title']; ?></b></h5>
    <p><?= $gallery['description']; ?></p><br/>
    <ul class="gallery">
        <?php foreach ($gallery['images'] as $image) { ?>
            <li>
                <a href="<?= $image['link']; ?>" target="_blank"><img src="<?= $image['thumb']; ?>"></a>
            </li>
        <?php } ?>
    </ul>
<?php } else { ?>
    <p><?= lang('sampoyigi.local::default.text_empty_gallery'); ?></p>
<?php } ?>
