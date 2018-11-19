<?php if (isset($gallery['images']) AND count($gallery['images'])) { ?>
    <h1 class="h4"><b><?= $gallery['title']; ?></b></h1>
    <p><?= $gallery['description']; ?></p><br/>
    <div class="row gallery">
        <?php foreach ($gallery['images'] as $media) { ?>
            <div class="col-sm-4">
                <img
                    class="img-responsive img-rounded"
                    src="<?= $media->getThumb(); ?>">
                <div class="overlay">
                    <a href="<?= $media->getPath(); ?>" target="_blank">
                        <i class="fa fa-eye"></i>
                    </a>
                </div>
            </div>
        <?php } ?>
    </div>
<?php } else { ?>
    <p><?= lang('igniter.local::default.text_empty_gallery'); ?></p>
<?php } ?>
