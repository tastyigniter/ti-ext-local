<?php

namespace Igniter\Local\Controllers;

use Admin\Facades\AdminMenu;
use Igniter\Local\Models\Reviews_model;

class Reviews extends \Admin\Classes\AdminController
{
    public $implement = [
        \Admin\Actions\ListController::class,
        \Admin\Actions\FormController::class,
        \Admin\Actions\LocationAwareController::class,
    ];

    public $listConfig = [
        'list' => [
            'model' => \Igniter\Local\Models\Reviews_model::class,
            'title' => 'lang:igniter.local::default.reviews.text_title',
            'emptyMessage' => 'lang:igniter.local::default.reviews.text_empty',
            'defaultSort' => ['review_id', 'DESC'],
            'configFile' => 'reviews_model',
        ],
    ];

    public $formConfig = [
        'name' => 'lang:igniter.local::default.reviews.text_form_name',
        'model' => \Igniter\Local\Models\Reviews_model::class,
        'request' => \Igniter\Local\Requests\Review::class,
        'create' => [
            'title' => 'lang:admin::lang.form.create_title',
            'redirect' => 'igniter/local/reviews/edit/{review_id}',
            'redirectClose' => 'igniter/local/reviews',
            'redirectNew' => 'igniter/local/reviews/create',
        ],
        'edit' => [
            'title' => 'lang:admin::lang.form.edit_title',
            'redirect' => 'igniter/local/reviews/edit/{review_id}',
            'redirectClose' => 'igniter/local/reviews',
            'redirectNew' => 'igniter/local/reviews/create',
        ],
        'preview' => [
            'title' => 'lang:admin::lang.form.preview_title',
            'redirect' => 'igniter/local/reviews',
        ],
        'delete' => [
            'redirect' => 'igniter/local/reviews',
        ],
        'configFile' => 'reviews_model',
    ];

    protected $requiredPermissions = 'Admin.Reviews';

    protected static $reviewHints;

    public function __construct()
    {
        parent::__construct();

        $this->hiddenActions[] = 'makeAverageRatingDataset';

        AdminMenu::setContext('reviews', 'sales');
    }

    public function index()
    {
        $this->addJs('~/app/admin/dashboardwidgets/charts/assets/vendor/chartjs/Chart.min.js', 'chartsjs-js');
        $this->addJs('~/app/admin/dashboardwidgets/charts/assets/vendor/chartjs/chartjs-adapter-moment.min.js', 'chartsjs-adapter-js');

        $this->addJs('$/igniter/local/assets/js/reviewchart.js', 'reviewchart-js');

        $this->asExtension('ListController')->index();
    }

    public function makeAverageRatingDataset($ratingType, $records)
    {
        if (is_null(self::$reviewHints))
            self::$reviewHints = Reviews_model::make()->getRatingOptions();

        $pieColors = ['', '#e74c3c', '#f1c40f', '#9b59b6', '#64B5F6', '#1abc9c'];

        $chartData = [
            'datasets' => [
                [
                    'data' => [],
                    'backgroundColor' => [],
                ],
            ],
            'labels' => array_values(self::$reviewHints),
        ];

        for ($rating = 1; $rating <= 5; $rating++) {
            $chartData['datasets'][0]['data'][] = $records->where($ratingType, $rating)->count();
            $chartData['datasets'][0]['backgroundColor'][] = $pieColors[$rating];
        }

        return $chartData;
    }
}
