<?php

namespace Igniter\Local\Components;

use Igniter\Local\Facades\Location;

class Search extends \System\Classes\BaseComponent
{
    use \Igniter\Local\Traits\SearchesNearby;
    use \Main\Traits\UsesPage;

    public function defineProperties()
    {
        return [
            'hideSearch' => [
                'label' => 'lang:igniter.local::default.label_location_search_mode',
                'type' => 'switch',
                'comment' => 'lang:igniter.local::default.help_location_search_mode',
                'validationRule' => 'required|boolean',
            ],
            'menusPage' => [
                'label' => 'Menu Page',
                'type' => 'select',
                'default' => 'local/menus',
                'options' => [static::class, 'getThemePageOptions'],
                'validationRule' => 'required|regex:/^[a-z0-9\-_\/]+$/i',
            ],
        ];
    }

    public function onRun()
    {
        $this->addJs('js/local.js', 'local-module-js');

        $this->prepareVars();
    }

    protected function prepareVars()
    {
        $this->page['menusPage'] = $this->property('menusPage');
        $this->page['hideSearch'] = $this->property('hideSearch', FALSE);
        $this->page['searchEventHandler'] = $this->getEventHandler('onSearchNearby');

        $this->page['searchQueryPosition'] = Location::instance()->userPosition();
    }
}
