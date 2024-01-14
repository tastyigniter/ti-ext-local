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
}
