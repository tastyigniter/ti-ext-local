<?php

namespace Igniter\Local\Models;

use Igniter\Flame\Database\Model;

class ReviewSettings extends Model
{
    public array $implement = [\Igniter\System\Actions\SettingsModel::class];

    // A unique code
    public string $settingsCode = 'igniter_review_settings';

    // Reference to field configuration
    public string $settingsFieldsConfig = 'reviewsettings';

    public static array $defaultHints = [
        ['value' => 'Poor'],
        ['value' => 'Average'],
        ['value' => 'Good'],
        ['value' => 'Very Good'],
        ['value' => 'Excellent'],
    ];

    public static function allowReviews()
    {
        return (bool)self::get('allow_reviews', true);
    }

    public static function autoApproveReviews()
    {
        return self::get('approve_reviews', false);
    }

    public static function getHints()
    {
        return collect(self::get('hints', self::$defaultHints))->pluck('value')->all();
    }
}
