<?php namespace Igniter\Local;

use Igniter\Cart\Models\CartSettings;
use Igniter\Local\Classes\Location as LocationManager;

class Extension extends \System\Classes\BaseExtension
{
    public $require = ['igniter.cart'];

    public function register()
    {
        $this->app->singleton('location', function ($app) {
            $location = new LocationManager($app['session.store'], $app['events']);

            $location->setDefaultLocation(params('default_location_id'));

            return $location;
        });

        $this->registerCartConditions();
    }

    public function registerCartConditions()
    {
        CartSettings::registerConditions(function (CartSettings $settingsModel) {
            $settingsModel->registerCondition('Igniter\Local\Conditions\Delivery', [
                'name' => 'delivery',
                'label' => 'lang:igniter.local::default.text_delivery',
                'description' => 'lang:igniter.local::default.help_delivery_condition',
            ]);
        });
    }

    public function registerComponents()
    {
        return [
            'Igniter\Local\Components\Local' => [
                'code' => 'local',
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
                'action' => ['manage'],
                'description' => 'Ability to manage local extension settings',
            ],
        ];
    }
}
