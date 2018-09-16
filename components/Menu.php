<?php

namespace Igniter\Local\Components;

use Admin\Models\Menus_model;
use Location;

class Menu extends \System\Classes\BaseComponent
{
    protected $location;

    public function defineProperties()
    {
        return [
            'menusPerPage' => [
                'label' => 'Menus Per Page',
                'type' => 'number',
                'default' => 20,
            ],
            'showMenuImages' => [
                'label' => 'Show Menu Item Images',
                'type' => 'switch',
            ],
            'menuImageWidth' => [
                'label' => 'lang:igniter.local::default.label_menu_image_height',
                'type' => 'number',
                'span' => 'left',
                'default' => 95,
            ],
            'menuImageHeight' => [
                'label' => 'lang:igniter.local::default.label_menu_image_width',
                'type' => 'number',
                'span' => 'right',
                'default' => 80,
            ],
        ];
    }

    public function onRun()
    {
        $this->page['showMenuImages'] = $this->property('showMenuImages');
        $this->page['menuImageWidth'] = $this->property('menuImageWidth');
        $this->page['menuImageHeight'] = $this->property('menuImageHeight');

        $this->page['menuList'] = $this->loadList();
    }

    protected function loadList()
    {
        $list = Menus_model::with(['mealtime', 'menu_options', 'special'])->listFrontEnd([
            'page' => $this->param('page'),
            'pageLimit' => $this->property('menusPerPage'),
            'sort' => $this->property('sort', 'menu_priority asc'),
            'location' => Location::getId(),
            'category' => $this->param('category'),
        ]);

        return $list;
    }
}