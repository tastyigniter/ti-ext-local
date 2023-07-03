<?php

namespace Igniter\Local\Models;

use Igniter\Flame\Database\Attach\HasMedia;
use Igniter\Flame\Database\Factories\HasFactory;
use Igniter\Flame\Database\Model;
use Igniter\Flame\Database\Traits\HasPermalink;
use Igniter\Flame\Database\Traits\Purgeable;
use Igniter\Local\Contracts\LocationInterface;
use Igniter\Local\Models\Concerns\HasDeliveryAreas;
use Igniter\Local\Models\Concerns\HasLocationSettings;
use Igniter\Local\Models\Concerns\HasWorkingHours;
use Igniter\Local\Models\Concerns\LocationHelpers;
use Igniter\System\Models\Concerns\Defaultable;
use Igniter\System\Models\Concerns\HasCountry;
use Igniter\System\Models\Concerns\Switchable;

/**
 * Location Model Class
 */
class Location extends Model implements LocationInterface
{
    use HasCountry;
    use HasWorkingHours;
    use HasDeliveryAreas;
    use HasFactory;
    use HasPermalink;
    use HasMedia;
    use HasLocationSettings;
    use LocationHelpers;
    use Purgeable;
    use Switchable;
    use Defaultable;

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
        if (!$defaultId = self::getDefaultKey()) {
            return false;
        }

        if (!$model = self::whereIsEnabled()->find($defaultId)) {
            return false;
        }

        return isset($model->getAddress()['location_lat'])
            && isset($model->getAddress()['location_lng'])
            && ($model->hasDelivery() || $model->hasCollection())
            && isset($model->options['hours'])
            && $model->delivery_areas()->whereIsDefault()->count() > 0;
    }

    //
    // Accessors & Mutators
    //

    public function getLocationThumbAttribute()
    {
        return $this->hasMedia() ? $this->getThumb() : null;
    }

    //
    // Helpers
    //

    public function setUrl($suffix = null)
    {
        if (is_single_location()) {
            $suffix = '/menus';
        }

        $this->url = page_url($this->permalink_slug.$suffix);
    }

    public function hasGallery()
    {
        return $this->hasMedia('gallery');
    }

    public function getGallery()
    {
        $gallery = array_get($this->options, 'gallery');
        $gallery['images'] = $this->getMedia('gallery');

        return $gallery;
    }

    public function defaultableName(): string
    {
        return $this->location_name;
    }
}
