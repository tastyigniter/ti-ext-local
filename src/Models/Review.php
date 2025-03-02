<?php

namespace Igniter\Local\Models;

use Igniter\Flame\Database\Factories\HasFactory;
use Igniter\Flame\Database\Model;
use Igniter\Flame\Exception\ApplicationException;
use Igniter\Local\Models\Concerns\Locationable;
use Igniter\System\Models\Concerns\Switchable;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Support\Facades\DB;

/**
 * Reviews Model Class
 */
class Review extends Model
{
    use HasFactory;
    use Locationable;
    use Switchable;

    public const SWITCHABLE_COLUMN = 'review_status';

    /**
     * @var string The database table name
     */
    protected $table = 'igniter_reviews';

    /**
     * @var string The database table primary key
     */
    protected $primaryKey = 'review_id';

    public $timestamps = true;

    protected $guarded = [];

    protected $casts = [
        'customer_id' => 'integer',
        'reviewable_id' => 'integer',
        'location_id' => 'integer',
        'quality' => 'integer',
        'service' => 'integer',
        'delivery' => 'integer',
        'review_status' => 'boolean',
    ];

    public $relation = [
        'belongsTo' => [
            'location' => [\Igniter\Local\Models\Location::class, 'scope' => 'isEnabled'],
            'customer' => \Igniter\User\Models\Customer::class,
        ],
        'morphTo' => [
            'reviewable' => ['name' => 'sale'],
        ],
    ];

    protected array $queryModifierSorts = [
        'created_at asc', 'created_at desc',
        'created_at asc', 'created_at desc',
    ];

    protected array $queryModifierFilters = [
        'location' => 'whereHasLocation',
        'customer' => 'applyCustomer',
    ];

    public static $relatedSaleTypes = [
        'orders' => \Igniter\Cart\Models\Order::class,
        'reservations' => \Igniter\Reservation\Models\Reservation::class,
    ];

    public static $ratingScoreCache = [];

    public static function getReviewableTypeOptions()
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

    public static function leaveReview(?Model $reviewable = null, array $data = [])
    {
        throw_unless($reviewable->isCompleted(), new ApplicationException(
            lang('igniter.local::default.review.alert_review_status_history')
        ));

        throw_if($reviewable->customer && self::checkReviewed($reviewable, $reviewable->customer), new ApplicationException(
            lang('igniter.local::default.review.alert_review_duplicate')
        ));

        $review = new static;
        $review->location_id = $reviewable->location_id;
        $review->customer_id = $reviewable->customer_id;
        $review->author = $reviewable->customer_name;
        $review->reviewable_id = $reviewable->getKey();
        $review->reviewable_type = $reviewable->getMorphClass();
        $review->quality = array_get($data, 'quality', 0);
        $review->delivery = array_get($data, 'delivery', 0);
        $review->service = array_get($data, 'service', 0);
        $review->review_text = array_get($data, 'review_text', '');

        if (!array_get($data, 'review_status') && ReviewSettings::autoApproveReviews()) {
            $review->review_status = true;
        }

        $review->save();

        return $review;
    }

    public function getRatingOptions()
    {
        return ReviewSettings::getHints();
    }

    //
    // Scopes
    //

    public function scopeIsApproved($query)
    {
        return $query->whereIsEnabled();
    }

    public function scopeHasBeenReviewed($query, $sale, $customerId)
    {
        return $query->where('reviewable_type', $sale->getMorphClass())
            ->where('reviewable_id', $sale->getKey())
            ->where('customer_id', $customerId);
    }

    public function scopeWhereReviewable($query, $causer)
    {
        return $query
            ->where('reviewable_type', $causer->getMorphClass())
            ->where('reviewable_id', $causer->getKey());
    }

    //
    // Helpers
    //

    public function getSaleTypeModel($saleType)
    {
        $model = self::$relatedSaleTypes[$saleType] ?? null;
        if (!$model || !class_exists($model)) {
            throw new ModelNotFoundException;
        }

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
        if (!$locationId) {
            return null;
        }

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
