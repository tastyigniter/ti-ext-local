<?php

namespace Igniter\Local\Models;

use Model;

class ReviewSettings extends Model
{
    public $implement = ['System\Actions\SettingsModel'];

    // A unique code
    public $settingsCode = 'igniter_review_settings';

    // Reference to field configuration
    public $settingsFieldsConfig = 'reviewsettings';
}
