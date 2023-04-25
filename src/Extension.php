<?php

namespace Igniter\Local;

use Igniter\Admin\DashboardWidgets\Charts;
use Igniter\Admin\Models\Location as LocationModel;
use Igniter\Admin\Models\LocationArea;
use Igniter\Admin\Models\Order;
use Igniter\Admin\Models\Reservation;
use Igniter\Flame\Geolite\Facades\Geocoder;
use Igniter\Local\Classes\Location;
use Igniter\Local\Classes\OrderTypes;
use Igniter\Local\Facades\Location as LocationFacade;
use Igniter\Local\Listeners\ExtendLocationOptions;
use Igniter\Local\Listeners\MaxOrderPerTimeslotReached;
use Igniter\Local\Models\Review;
use Igniter\Local\Models\ReviewSettings;
use Igniter\Main\Facades\Auth;
use Illuminate\Database\Eloquent\Relations\Relation;
use Illuminate\Foundation\AliasLoader;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\View;

class Extension extends \Igniter\System\Classes\BaseExtension
{
    public function register()
    {
        $this->app->singleton('location', Location::class);
        $this->app->singleton(OrderTypes::class);

        $aliasLoader = AliasLoader::getInstance();
        $aliasLoader->alias('Location', Facades\Location::class);
    }

    public function boot()
    {
        Event::subscribe(ExtendLocationOptions::class);
        Event::subscribe(MaxOrderPerTimeslotReached::class);

        Event::listen('router.beforeRoute', function ($url, $router) {
            View::share('showReviews', (bool)ReviewSettings::get('allow_reviews', false));
        });

        $this->bindRememberLocationAreaEvents();

        $this->addReviewsRelationship();
        $this->addAssetsToReviewsSettingsPage();
        $this->extendDashboardChartsDatasets();
    }

    public function registerAutomationRules()
    {
        return [
            'events' => [],
            'actions' => [],
            'conditions' => [
                \Igniter\Local\AutomationRules\Conditions\ReviewCount::class,
            ],
            'presets' => [
                'chase_review_after_one_day' => [
                    'name' => 'Send a message to leave a review after 24 hours',
                    'event' => \Igniter\Automation\AutomationRules\Events\OrderSchedule::class,
                    'actions' => [
                        \Igniter\Automation\AutomationRules\Actions\SendMailTemplate::class => [
                            'template' => 'igniter.local::mail.review_chase',
                            'send_to' => 'customer',
                        ],
                    ],
                    'conditions' => [
                        \Igniter\Local\AutomationRules\Conditions\ReviewCount::class => [
                            [
                                'attribute' => 'review_count',
                                'value' => '0',
                                'operator' => 'is',
                            ],
                        ],
                        \Igniter\Cart\AutomationRules\Conditions\OrderAttribute::class => [
                            [
                                'attribute' => 'hours_since',
                                'value' => '24',
                                'operator' => 'is',
                            ],
                        ],
                    ],
                ],
            ],
        ];
    }

    public function registerCartConditions()
    {
        return [
            \Igniter\Local\CartConditions\Delivery::class => [
                'name' => 'delivery',
                'label' => 'lang:igniter.local::default.text_delivery',
                'description' => 'lang:igniter.local::default.help_delivery_condition',
            ],
        ];
    }

    public function registerComponents()
    {
        return [
            \Igniter\Local\Components\LocalBox::class => [
                'code' => 'localBox',
                'name' => 'lang:igniter.local::default.component_title',
                'description' => 'lang:igniter.local::default.component_desc',
            ],
            \Igniter\Local\Components\Search::class => [
                'code' => 'localSearch',
                'name' => 'lang:igniter.local::default.search.component_title',
                'description' => 'lang:igniter.local::default.search.component_desc',
            ],
            \Igniter\Local\Components\Menu::class => [
                'code' => 'localMenu',
                'name' => 'lang:igniter.local::default.menu.component_title',
                'description' => 'lang:igniter.local::default.menu.component_desc',
            ],
            \Igniter\Local\Components\Categories::class => [
                'code' => 'categories',
                'name' => 'lang:igniter.local::default.categories.component_title',
                'description' => 'lang:igniter.local::default.categories.component_desc',
            ],
            \Igniter\Local\Components\Review::class => [
                'code' => 'localReview',
                'name' => 'lang:igniter.local::default.review.component_title',
                'description' => 'lang:igniter.local::default.review.component_desc',
            ],
            \Igniter\Local\Components\Info::class => [
                'code' => 'localInfo',
                'name' => 'lang:igniter.local::default.info.component_title',
                'description' => 'lang:igniter.local::default.info.component_desc',
            ],
            \Igniter\Local\Components\Gallery::class => [
                'code' => 'localGallery',
                'name' => 'lang:igniter.local::default.gallery.component_title',
                'description' => 'lang:igniter.local::default.gallery.component_desc',
            ],
            \Igniter\Local\Components\LocalList::class => [
                'code' => 'localList',
                'name' => 'lang:igniter.local::default.list.component_title',
                'description' => 'lang:igniter.local::default.list.component_desc',
            ],
        ];
    }

    public function registerImportExport()
    {
        return [
            'import' => [
                'menus' => [
                    'label' => 'Import Menu Items',
                    'model' => \Igniter\Local\Models\MenuImport::class,
                    'configFile' => '$/igniter/local/models/config/menuimport',
                ],
            ],
            'export' => [
                'menus' => [
                    'label' => 'Export Menu Items',
                    'model' => \Igniter\Local\Models\MenuExport::class,
                    'configFile' => '$/igniter/local/models/config/menuexport',
                ],
            ],
        ];
    }

    public function registerMailTemplates()
    {
        return [
            'igniter.local::mail.review_chase' => 'lang:igniter.local::default.reviews.text_chase_email',
        ];
    }

    public function registerNavigation()
    {
        return [
            'marketing' => [
                'child' => [
                    'reviews' => [
                        'priority' => 30,
                        'class' => 'reviews',
                        'href' => admin_url('igniter/local/reviews'),
                        'title' => lang('lang:igniter.local::default.reviews.side_menu'),
                        'permission' => 'Admin.Reviews',
                    ],
                ],
            ],
        ];
    }

    public function registerPermissions()
    {
        return [
            'Admin.Reviews' => [
                'description' => 'lang:igniter.local::default.reviews.permissions',
                'group' => 'module',
            ],
        ];
    }

    public function registerSettings()
    {
        return [
            'reviewsettings' => [
                'label' => 'lang:igniter.local::default.reviews.text_settings',
                'icon' => 'fa fa-gear',
                'description' => 'lang:igniter.local::default.reviews.text_settings_description',
                'model' => \Igniter\Local\Models\ReviewSettings::class,
                'permissions' => ['Admin.Reviews'],
            ],
        ];
    }

    public function registerFormWidgets()
    {
        return [
            \Igniter\Local\FormWidgets\StarRating::class => [
                'label' => 'Star Rating',
                'code' => 'starrating',
            ],
        ];
    }

    public function registerScheduleTypes()
    {
        return [
            \Igniter\Local\ScheduleTypes\Delivery::class => [
                'code' => LocationModel::DELIVERY,
                'name' => 'lang:igniter.local::default.text_delivery',
            ],
            \Igniter\Local\ScheduleTypes\Collection::class => [
                'code' => LocationModel::COLLECTION,
                'name' => 'lang:igniter.local::default.text_collection',
            ],
        ];
    }

    protected function extendDashboardChartsDatasets()
    {
        Event::listen('admin.charts.extendDatasets', function ($widget) {
            if (!$widget instanceof Charts) {
                return;
            }

            if (!ReviewSettings::get('allow_reviews', false)) {
                return;
            }

            $widget->contextDefinitions['reviews'] = [
                'label' => 'lang:igniter.local::default.reviews.text_title',
                'color' => '#FFB74D',
                'model' => Review::class,
                'column' => 'created_at',
            ];
        });
    }

    protected function addAssetsToReviewsSettingsPage()
    {
        Event::listen('admin.form.extendFieldsBefore', function ($form) {
            if (!$form->model instanceof ReviewSettings) {
                return;
            }

            $form->addJs('~/app/admin/formwidgets/repeater/assets/vendor/sortablejs/Sortable.min.js', 'sortable-js');
            $form->addJs('~/app/admin/formwidgets/repeater/assets/vendor/sortablejs/jquery-sortable.js', 'jquery-sortable-js');
            $form->addJs('~/app/admin/assets/js/ratings.js', 'ratings-js');
        });
    }

    protected function addReviewsRelationship(): void
    {
        Relation::morphMap([
            'reviews' => \Igniter\Local\Models\Review::class,
        ]);

        Order::extend(function ($model) {
            $model->relation['morphMany']['review'] = [\Igniter\Local\Models\Review::class];
        });

        Reservation::extend(function ($model) {
            $model->relation['morphMany']['review'] = [\Igniter\Local\Models\Review::class];
        });

        LocationModel::extend(function ($model) {
            $model->relation['hasMany']['reviews'] = [\Igniter\Local\Models\Review::class];

            $model->addDynamicMethod('reviews_score', function () use ($model) {
                return Review::getScoreForLocation($model->getKey());
            });
        });

        LocationModel::addSortingColumns(['reviews_count asc', 'reviews_count desc']);
    }

    protected function bindRememberLocationAreaEvents(): void
    {
        Event::listen('location.position.updated', function ($location, $position, $oldPosition) {
            $this->updateCustomerLastArea([
                'query' => $position->format(),
            ]);
        });

        Event::listen('location.area.updated', function ($location, $coveredArea) {
            $this->updateCustomerLastArea([
                'areaId' => $coveredArea->getKey(),
            ]);
        });

        Event::listen(['igniter.user.login', 'igniter.socialite.login'], function () {
            try {
                if (!strlen($lastArea = Auth::customer()->last_location_area)) {
                    return;
                }

                $lastArea = json_decode($lastArea, true);

                $searchQuery = array_get($lastArea, 'query');
                if ($searchQuery && $userPosition = Geocoder::geocode($searchQuery)->first()) {
                    LocationFacade::updateUserPosition($userPosition);
                }

                $areaId = array_get($lastArea, 'areaId');
                if ($areaId && $area = LocationArea::find($areaId)) {
                    LocationFacade::updateNearbyArea($area);
                }
            } catch (\Exception $exception) {
            }
        });
    }

    protected function updateCustomerLastArea($value)
    {
        if (!$customer = Auth::customer()) {
            return;
        }

        $lastArea = @json_decode($customer->last_location_area, true) ?: [];
        $lastArea = array_merge($lastArea, $value);

        $customer->update([
            'last_location_area' => json_encode($lastArea),
        ]);
    }
}
