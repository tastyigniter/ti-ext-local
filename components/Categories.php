<?php namespace Igniter\Local\Components;

use Admin\Models\Categories_model;
use Location;
use Main\Template\Page;

class Categories extends \System\Classes\BaseComponent
{
    use \Main\Traits\HasPageOptions;

    public function defineProperties()
    {
        return [
            'menusPage' => [
                'label' => 'Menu Page',
                'type' => 'select',
                'default' => 'local/menus',
                'options' => [static::class, 'getPageOptions'],
            ],
        ];
    }

    public static function getMenusPageOptions()
    {
        return Page::lists('baseFileName', 'baseFileName');
    }

    public function onRun()
    {
        $this->page['menusPage'] = $this->property('menusPage');
        $this->page['categories'] = $this->loadCategories();
        $this->page['selectedCategory'] = $this->findSelectedCategory();
    }

    protected function loadCategories()
    {
        $query = Categories_model::with(['children', 'children.children'])->isEnabled()->sorted();

        if ($location = Location::current())
            $query->whereHasOrDoesntHaveLocation($location->getKey());

        return $query->get();
    }

    protected function findSelectedCategory()
    {
        $slug = $this->param('category');
        if (!strlen($slug))
            return null;

        return Categories_model::isEnabled()->where('permalink_slug', $slug)->first();
    }
}
