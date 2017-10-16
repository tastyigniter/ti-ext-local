<?php namespace SamPoyigi\Local\Models;

use Model;

class Settings_model extends Model
{
    public $implement = ['System\Actions\SettingsModel'];

    // A unique code
    public $settingsCode = 'sampoyigi.local';

    // Reference to field configuration
    public $settingsFieldsConfig = 'settings_model';
}
