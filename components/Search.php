<?php namespace SamPoyigi\Local\Components;

use Location;

class Search extends \System\Classes\BaseComponent
{
    use \SamPoyigi\Local\Traits\SearchesNearby;

    public function defineProperties()
    {
        return [
            'hideSearch' => [
                'label'   => 'lang:sampoyigi.local::default.label_location_search_mode',
                'type'    => 'switch',
                'comment' => 'lang:sampoyigi.local::default.help_location_search_mode',
            ],
            'menusPage'  => [
                'label'   => 'Menu Page',
                'type'    => 'text',
                'default' => 'local/menus',
            ],
        ];
    }

    public function onRun()
    {
        $this->addCss('css/local.css', 'local-css');
        $this->addJs('js/local.js', 'local-module-js');

        $this->prepareVars();
    }

    protected function prepareVars()
    {
        $this->page['menusPage'] = $this->property('menusPage');
        $this->page['hideSearch'] = $this->property('hideSearch', FALSE);

        $this->page['searchEventHandler'] = $this->getEventHandler('onSearchNearby');
        $this->page['currentLocation'] = Location::current();
        $this->page['userPosition'] = Location::userPosition();
    }
}
