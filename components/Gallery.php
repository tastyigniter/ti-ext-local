<?php

namespace Igniter\Local\Components;

use Location;

class Gallery extends \System\Classes\BaseComponent
{
    public $isHidden = TRUE;

    public function onRun()
    {
        $locationCurrent = Location::current();
        $gallery = $locationCurrent->getGallery();

        $this->id = uniqid($this->alias);
        $this->page['gallery'] = $gallery;
    }
}