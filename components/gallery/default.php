<?php if (count($galleryImages)) { ?>
    <h5><b><?= $localGallery['title']; ?></b></h5>
    <p><?= $localGallery['description']; ?></p><br/>
    <ul class="gallery">
        <?php foreach ($galleryImages as $image) { ?>
            <li>
                <a href="<?= $image['link']; ?>" target="_blank"><img src="<?= $image['thumb']; ?>"></a>
            </li>
        <?php } ?>
    </ul>
<?php } else { ?>
    <p><?= lang('sampoyigi.local::default.text_empty_gallery'); ?></p>
<?php } ?>
