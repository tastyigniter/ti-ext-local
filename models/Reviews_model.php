<?php

namespace Igniter\Local\Models;

use Admin\Traits\Locationable;
use Igniter\Flame\Auth\Models\User;
use Igniter\Flame\Database\Model;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Support\Facades\DB;

/**
 * Reviews Model Class
 */
class Reviews_model extends Model
{
    use Locationable;

    /**
     * @var string The database table name
     */
    protected $table = 'igniter_reviews';

    /**
     * @var string The database table primary key
     */
    protected $primaryKey = 'review_id';

    /**
     * @var array The model table column to convert to dates on insert/update
     */
    public $timestamps = true;

    protected $guarded = [];

    protected $casts = [
        'customer_id' => 'integer',
        'sale_id' => 'integer',
        'location_id' => 'integer',
        'quality' => 'integer',
        'service' => 'integer',
        'delivery' => 'integer',
        'review_status' => 'boolean',
    ];

    public $relation = [
        'belongsTo' => [
            'location' => [\Admin\Models\Locations_model::class, 'scope' => 'isEnabled'],
            'customer' => \Admin\Models\Customers_model::class,
        ],
        'morphTo' => [
            'reviewable' => ['name' => 'sale'],
        ],
    ];

    public static $allowedSortingColumns = ['created_at asc', 'created_at desc'];

    public static $relatedSaleTypes = [
        'orders' => \Admin\Models\Orders_model::class,
        'reservations' => \Admin\Models\Reservations_model::class,
    ];

    public static $ratingScoreCache = [];

    public static function getSaleTypeOptions()
    {
        return [
            'orders' => 'lang:igniter.local::default.reviews.text_order',
            'reservations' => 'lang:igniter.local::default.reviews.text_reservation',
        ];
    }

    public static function findBy($saleType, $saleId)
    {
        $saleTypeModel = (new static)->getSaleTypeModel($saleType);

        return $saleTypeModel->find($saleId);
    }

    public function getRatingOptions()
    {
        return array_get(ReviewSettings::get('ratings'), 'ratings', []);
    }

    //
    // Scopes
    //

    public function scopeListFrontEnd($query, $options = [])
    {
        extract(array_merge([
            'page' => 1,
            'pageLimit' => 20,
            'sort' => null,
            'location' => null,
            'customer' => null,
        ], $options));

        if (is_numeric($location)) {
            $query->where('location_id', $location);
        }

        if ($customer instanceof User) {
            $query->where('customer_id', $customer->getKey());
        }
        elseif (strlen($customer)) {
            $query->where('customer_id', $customer);
        }
        else {
            $query->has('customer');
        }

        if (!is_array($sort)) {
            $sort = [$sort];
        }

        foreach ($sort as $_sort) {
            if (in_array($_sort, self::$allowedSortingColumns)) {
                $parts = explode(' ', $_sort);
                if (count($parts) < 2) {
                    array_push($parts, 'desc');
                }
                [$sortField, $sortDirection] = $parts;
                $query->orderBy($sortField, $sortDirection);
            }
        }

        $this->fireEvent('model.extendListFrontEndQuery', [$query]);

        return $query->paginate($pageLimit, $page);
    }

    public function scopeIsApproved($query)
    {
        return $query->where('review_status', 1);
    }

    public function scopeHasBeenReviewed($query, $sale, $customerId)
    {
        return $query->where('sale_type', $sale->getMorphClass())
            ->where('sale_id', $sale->getKey())
            ->where('customer_id', $customerId);
    }

    public function scopeWhereReviewable($query, $causer)
    {
        return $query
            ->where('sale_type', $causer->getMorphClass())
            ->where('sale_id', $causer->getKey());
    }

    //
    // Helpers
    //

    public function getSaleTypeModel($saleType)
    {
        $model = self::$relatedSaleTypes[$saleType] ?? null;
        if (!$model || !class_exists($model))
            throw new ModelNotFoundException;

        return new $model();
    }

    /**
     * Return the dates of all reviews
     *
     * @return array
     */
    public function getReviewDates()
    {
        return $this->pluckDates('created_at');
    }

    public static function checkReviewed(Model $object, Model $customer)
    {
        $query = self::whereReviewable($object)
            ->where('customer_id', $customer->getKey());

        return $query->exists();
    }

    public static function getScoreForLocation($locationId)
    {
        if (!$locationId)
            return null;

        if (!$ratings = array_get(self::$ratingScoreCache, $locationId)) {
            $ratings = DB::table(self::make()->getTable())
                ->selectRaw('SUM(delivery = 1) as dr1, SUM(delivery = 2) as dr2, SUM(delivery = 3) as dr3, SUM(delivery = 4) as dr4, SUM(delivery = 5) as dr5')
                ->selectRaw('SUM(quality = 1) as qr1, SUM(quality = 2) as qr2, SUM(quality = 3) as qr3, SUM(quality = 4) as qr4, SUM(quality = 5) as qr5')
                ->selectRaw('SUM(service = 1) as sr1, SUM(service = 2) as sr2, SUM(service = 3) as sr3, SUM(service = 4) as sr4, SUM(service = 5) as sr5')
                ->where('location_id', $locationId)
                ->get()->toArray();

            self::$ratingScoreCache[$locationId] = $ratings;
        }

        $ratings = (array)array_shift($ratings);

        $totalWeight = 0;
        $totalReviews = 0;

        foreach ($ratings as $rating => $totalRatings) {
            $rating = (int)substr($rating, 2);
            $totalRatings = (int)$totalRatings;

            $weight = $rating * $totalRatings;
            $totalWeight += $weight;
            $totalReviews += $totalRatings;
        }

        return $totalReviews > 0 ? ($totalWeight / $totalReviews) : 0;
    }
}
