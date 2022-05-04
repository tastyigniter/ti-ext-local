<?php

namespace Igniter\Local\Components;

use Admin\Models\Categories_model;
use Igniter\Local\Facades\Location;

class Categories extends \System\Classes\BaseComponent
{
    use \Main\Traits\UsesPage;

    public function defineProperties()
    {
        return [
            'menusPage' => [
                'label' => 'Menu Page',
                'type' => 'select',
                'default' => 'local'.DIRECTORY_SEPARATOR.'menus',
                'options' => [static::class, 'getThemePageOptions'],
                'validationRule' => 'required|regex:/^[a-z0-9\-_\/]+$/i',
            ],
            'hideEmptyCategory' => [
                'label' => 'Hide categories with no items from the list',
                'type' => 'switch',
                'default' => false,
                'validationRule' => 'required|boolean',
            ],
            'hiddenCategories' => [
                'label' => 'Categories to hide from the list',
                'type' => 'selectlist',
                'options' => [Categories_model::class, 'getDropdownOptions'],
                'placeholder' => 'lang:admin::lang.text_please_select',
                'validationRule' => 'array',
            ],
        ];
    }

    public function onRun()
    {
        $this->page['menusPage'] = $this->property('menusPage');
        $this->page['hideEmptyCategory'] = (bool)$this->property('hideEmptyCategory', false);
        $this->page['hiddenCategories'] = $this->property('hiddenCategories') ?? [];

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

        $query = Categories_model::isEnabled()->where('permalink_slug', $slug);

        if ($location = Location::current())
            $query->whereHasOrDoesntHaveLocation($location->getKey());

        return $query->first();
    }
}
