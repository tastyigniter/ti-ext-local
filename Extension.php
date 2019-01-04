<?php namespace Igniter\Local;

use Igniter\Local\Classes\Location;
use Illuminate\Foundation\AliasLoader;

class Extension extends \System\Classes\BaseExtension
{
    public function register()
    {
        $this->app->singleton('location', Location::class);

        $aliasLoader = AliasLoader::getInstance();
        $aliasLoader->alias('Location', Facades\Location::class);
    }

    public function registerCartConditions()
    {
        return [
            'Igniter\Local\Conditions\Delivery' => [
                'name' => 'delivery',
                'label' => 'lang:igniter.local::default.text_delivery',
                'description' => 'lang:igniter.local::default.help_delivery_condition',
            ],
        ];
    }

    public function registerApiResources()
    {
        return [
            'menus' => [
                'name' => 'Menus',
                'description' => 'An API resource for menus',
                'model' => \Admin\Models\Menus_model::class,
                'controller' => \Igniter\Local\Resources\Menus::class,
                'transformer' => \Igniter\Local\Resources\Transformers\MenuTransformer::class,
            ],
            'categories' => [
                'name' => 'Categories',
                'description' => 'An API resource for categories',
                'model' => \Admin\Models\Categories_model::class,
                'controller' => \Igniter\Local\Resources\Categories::class,
                'transformer' => \Igniter\Local\Resources\Transformers\CategoryTransformer::class,
            ],
            'locations' => [
                'name' => 'Locations',
                'description' => 'An API resource for locations',
                'model' => \Admin\Models\Locations_model::class,
                'controller' => \Igniter\Local\Resources\Locations::class,
                'transformer' => \Igniter\Local\Resources\Transformers\LocationTransformer::class,
            ],
        ];
    }

    public function registerComponents()
    {
        return [
            'Igniter\Local\Components\LocalBox' => [
                'code' => 'localBox',
                'name' => 'lang:igniter.local::default.component_title',
                'description' => 'lang:igniter.local::default.component_desc',
            ],
            'Igniter\Local\Components\Search' => [
                'code' => 'localSearch',
                'name' => 'lang:igniter.local::default.search.component_title',
                'description' => 'lang:igniter.local::default.search.component_desc',
            ],
            'Igniter\Local\Components\Menu' => [
                'code' => 'localMenu',
                'name' => 'lang:igniter.local::default.menu.component_title',
                'description' => 'lang:igniter.local::default.menu.component_desc',
            ],
            'Igniter\Local\Components\Categories' => [
                'code' => 'categories',
                'name' => 'lang:igniter.local::default.categories.component_title',
                'description' => 'lang:igniter.local::default.categories.component_desc',
            ],
            'Igniter\Local\Components\Review' => [
                'code' => 'localReview',
                'name' => 'lang:igniter.local::default.review.component_title',
                'description' => 'lang:igniter.local::default.review.component_desc',
            ],
            'Igniter\Local\Components\Info' => [
                'code' => 'localInfo',
                'name' => 'lang:igniter.local::default.info.component_title',
                'description' => 'lang:igniter.local::default.info.component_desc',
            ],
            'Igniter\Local\Components\Gallery' => [
                'code' => 'localGallery',
                'name' => 'lang:igniter.local::default.gallery.component_title',
                'description' => 'lang:igniter.local::default.gallery.component_desc',
            ],
            'Igniter\Local\Components\LocalList' => [
                'code' => 'localList',
                'name' => 'lang:igniter.local::default.list.component_title',
                'description' => 'lang:igniter.local::default.list.component_desc',
            ],
        ];
    }

    public function registerPermissions()
    {
        return [
            'Module.LocalModule' => [
                'group' => 'module',
                'description' => 'Ability to manage local extension settings',
            ],
        ];
    }
}
