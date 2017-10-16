<?php

namespace SamPoyigi\Local\Components;

use Igniter\Models\Image_tool_model;

class Gallery extends \System\Classes\BaseComponent
{
    public $isHidden = TRUE;

    public function onRun()
    {
        $this->prepareVars();
    }

    protected function prepareVars()
    {
        if (!$library = $this->property('library'))
            throw new \Exception("Missing [location library] property in {$this->alias} component");

        $gallery = $library->getGallery();

        $this->id = uniqid($this->alias);
        $this->page['localGallery'] = $gallery;
        $this->page['galleryImages'] = $this->prepareImages($gallery);
    }

    protected function prepareImages($gallery)
    {
        if (!isset($gallery['images']))
            return [];

        $images = [];
        foreach ($gallery['images'] as $image) {
            if (strlen($image) > 0) {
                $images[] = [
                    'link'  => image_url('data'.$image),
                    'thumb' => Image_tool_model::resize($image, ['height' => 200]),
                ];
            }
        }

        return $images;
    }
}