<?php

namespace Igniter\Local\Components;

use Admin\Models\Menus_model;
use Location;

class Menu extends \System\Classes\BaseComponent
{
    protected $location;

    protected $menuListCategories = [];

    public function defineProperties()
    {
        return [
            'isGrouped' => [
                'label' => 'Group menu items list by category',
                'type' => 'switch',
            ],
            'menusPerPage' => [
                'label' => 'Menus Per Page',
                'type' => 'number',
                'default' => 20,
            ],
            'showMenuImages' => [
                'label' => 'Show Menu Item Images',
                'type' => 'switch',
                'default' => FALSE,
            ],
            'menuImageWidth' => [
                'label' => 'lang:igniter.local::default.label_local_image_width',
                'type' => 'number',
                'span' => 'left',
                'default' => 95,
            ],
            'menuImageHeight' => [
                'label' => 'lang:igniter.local::default.label_local_image_height',
                'type' => 'number',
                'span' => 'right',
                'default' => 80,
            ],
        ];
    }

    public function onRun()
    {
        $this->page['menuIsGrouped'] = $this->property('isGrouped');
        $this->page['showMenuImages'] = $this->property('showMenuImages');
        $this->page['menuImageWidth'] = $this->property('menuImageWidth');
        $this->page['menuImageHeight'] = $this->property('menuImageHeight');
        $this->page['menuCategoryWidth'] = $this->property('menuCategoryWidth', 1240);
        $this->page['menuCategoryHeight'] = $this->property('menuCategoryHeight', 256);

        $this->page['menuList'] = $this->loadList();
        $this->page['menuListCategories'] = $this->menuListCategories;
    }

    protected function loadList()
    {
        $list = Menus_model::with(['mealtime', 'menu_options', 'categories', 'categories.media', 'special'])->listFrontEnd([
            'page' => $this->param('page'),
            'pageLimit' => $this->property('menusPerPage'),
            'sort' => $this->property('sort', 'menu_priority asc'),
            'location' => $this->getLocation(),
            'category' => $this->param('category'),
        ]);

        if ($this->property('isGrouped'))
            $this->groupListByCategory($list);

        return $list;
    }

    protected function getLocation()
    {
        if (!$location = Location::current())
            return null;

        return $location->getKey();
    }

    protected function groupListByCategory($list)
    {
        $this->menuListCategories = [];

        $collection = $list->getCollection()->mapToGroups(function ($menu) {
            $categories = [];
            foreach ($menu->categories as $category) {
                $this->menuListCategories[$category->getKey()] = $category;
                $categories[$category->getKey()] = $menu;
            }

            if (!$categories)
                $categories[] = $menu;

            return $categories;
        });

        $list->setCollection($collection);
    }
}