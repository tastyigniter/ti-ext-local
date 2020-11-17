<?php

namespace Igniter\Local\Models;

use Model;

class LocalSettings extends Model
{
    public $implement = ['System\Actions\SettingsModel'];

    // A unique code
    public $settingsCode = 'igniter_local_settings';

    // Reference to field configuration
    public $settingsFieldsConfig = 'localsettings';    
}
