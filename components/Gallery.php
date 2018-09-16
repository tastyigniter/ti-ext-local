<?php

namespace Igniter\Local\Components;

use Location;
use Main\Models\Image_tool_model;

class Gallery extends \System\Classes\BaseComponent
{
    public $isHidden = TRUE;

    public function onRun()
    {
        $currentLocation = Location::current();
        $gallery = $currentLocation->getGallery();

        $gallery = $this->processImages($gallery);

        $this->id = uniqid($this->alias);
        $this->page['gallery'] = $gallery;
    }

    protected function processImages($gallery)
    {
        $images = [];
        if (isset($gallery['images'])) {
            foreach ($gallery['images'] as $image) {
                if (strlen($image) > 0) {
                    $images[] = [
                        'link'  => image_url('data'.$image),
                        'thumb' => Image_tool_model::resize($image),
                    ];
                }
            }
        }

        $gallery['images'] = $images;

        return $gallery;
    }
}