<?php

namespace Igniter\Local\Models;

use Igniter\Flame\Database\Attach\HasMedia;
use Igniter\Flame\Database\Factories\HasFactory;
use Igniter\Flame\Database\Model;
use Igniter\Flame\Database\Traits\HasPermalink;
use Igniter\Flame\Database\Traits\Purgeable;
use Igniter\Local\Contracts\LocationInterface;
use Igniter\Local\Models\Concerns\HasDeliveryAreas;
use Igniter\Local\Models\Concerns\HasWorkingHours;
use Igniter\Local\Models\Concerns\LocationHelpers;
use Igniter\System\Models\Concerns\Defaultable;
use Igniter\System\Models\Concerns\HasCountry;
use Igniter\System\Models\Concerns\Switchable;

/**
 * Location Model Class
 *
 * @property int $location_id
 * @property string $location_name
 * @property string $location_email
 * @property string|null $description
 * @property string|null $location_address_1
 * @property string|null $location_address_2
 * @property string|null $location_city
 * @property string|null $location_state
 * @property string|null $location_postcode
 * @property int|null $location_country_id
 * @property string|null $location_telephone
 * @property float|null $location_lat
 * @property float|null $location_lng
 * @property int|null $location_radius
 * @property bool|null $location_status
 * @property string|null $permalink_slug
 * @property bool $is_default
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property int $is_auto_lat_lng
 * @property-read mixed|null $grouped_settings
 * @property-read mixed $location_thumb
 * @property-read mixed $thumb
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \Igniter\Flame\Database\Attach\Media> $media
 * @property-read int|null $media_count
 * @method static Location first()
 * @mixin \Igniter\Flame\Database\Model
 */
class Location extends Model implements LocationInterface
{
    use Defaultable;
    use HasCountry;
    use HasDeliveryAreas;
    use HasFactory;
    use HasMedia;
    use HasPermalink;
    use HasWorkingHours;
    use LocationHelpers;
    use Purgeable;
    use Switchable;

    public const SWITCHABLE_COLUMN = 'location_status';

    const KM_UNIT = 111.13384;

    const M_UNIT = 69.05482;

    const OPENING = 'opening';

    const RESERVATION = 'reservation';

    const DELIVERY = 'delivery';

    const COLLECTION = 'collection';

    const LOCATION_CONTEXT_SINGLE = 'single';

    const LOCATION_CONTEXT_MULTIPLE = 'multiple';

    /**
     * @var string The database table name
     */
    protected $table = 'locations';

    /**
     * @var string The database table primary key
     */
    protected $primaryKey = 'location_id';

    public $timestamps = true;

    protected $appends = ['location_thumb'];

    protected $casts = [
        'location_country_id' => 'integer',
        'location_lat' => 'double',
        'location_lng' => 'double',
    ];

    public $relation = [
        'hasMany' => [
            'settings' => [\Igniter\Local\Models\LocationSettings::class, 'delete' => true],
            'reviews' => [\Igniter\Local\Models\Review::class],
            'working_hours' => [\Igniter\Local\Models\WorkingHour::class, 'delete' => true],
        ],
        'belongsTo' => [
            'country' => [\Igniter\System\Models\Country::class, 'otherKey' => 'country_id', 'foreignKey' => 'location_country_id'],
        ],
    ];

    protected array $purgeable = ['options'];

    public array $permalinkable = [
        'permalink_slug' => [
            'source' => 'location_name',
            'controller' => 'local',
        ],
    ];

    public array $mediable = [
        'thumb',
        'gallery' => ['multiple' => true],
    ];

    protected array $queryModifierFilters = [
        'enabled' => 'applySwitchable',
        'position' => 'applyPosition',
    ];

    protected array $queryModifierSorts = [
        'distance asc', 'distance desc',
        'location_id asc', 'location_id desc',
        'location_name asc', 'location_name desc',
        'reviews_count asc', 'reviews_count desc',
    ];

    protected array $queryModifierSearchableFields = [
        'location_name', 'location_address_1',
        'location_address_2', 'location_city',
        'location_state', 'location_postcode',
        'description',
    ];

    public $url;

    public static function getDropdownOptions()
    {
        return static::whereIsEnabled()->dropdown('location_name');
    }

    public static function onboardingIsComplete()
    {
        if (!$model = self::getDefault()) {
            return false;
        }

        return isset($model->getAddress()['location_lat'], $model->getAddress()['location_lng'])
            && $model->delivery_areas()->whereIsDefault()->count() > 0;
    }

    public function getLocationThumbAttribute()
    {
        return $this->hasMedia() ? $this->getThumb() : null;
    }

    public function defaultableName(): string
    {
        return $this->location_name;
    }

    public function getMorphClass()
    {
        return 'locations';
    }
}
