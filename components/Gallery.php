<?php

namespace Igniter\Local\Components;

use Igniter\Local\Facades\Location;

class Gallery extends \System\Classes\BaseComponent
{
    public $isHidden = true;

    public function onRun()
    {
        $locationCurrent = Location::current();
        $gallery = $locationCurrent->getGallery();

        $this->id = uniqid($this->alias);
        $this->page['gallery'] = $gallery;
    }
}
