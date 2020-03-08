<?php

namespace Igniter\Local\Models;

use Igniter\Flame\Database\Attach\HasMedia;
use Igniter\ImportExport\Models\ExportModel;

class MenuExport extends ExportModel
{
    use HasMedia;

    protected $table = 'menus';

    protected $primaryKey = 'menu_id';

    public $relation = [
        'belongsTo' => [
            'menu_mealtime' => ['Admin\Models\Mealtimes_model', 'foreignKey' => 'menu_id'],
        ],
        'belongsToMany' => [
            'menu_categories' => ['Admin\Models\Categories_model', 'table' => 'menu_categories', 'foreignKey' => 'menu_id'],
        ],
    ];

    public $mediable = ['thumb'];

    /**
     * The accessors to append to the model's array form.
     * @var array
     */
    protected $appends = [
        'categories',
        'thumb_url',
        'mealtime',
    ];

    public function exportData($columns)
    {
        return self::make()->with([
            'menu_mealtime',
            'menu_categories',
            'media',
        ])->get()->toArray();
    }

    public function getCategoriesAttribute()
    {
        if (!$this->menu_categories) {
            return '';
        }

        return $this->encodeArrayValue($this->menu_categories->pluck('name')->all());
    }

    public function getThumbUrlAttribute()
    {
        if (!$this->hasMedia('thumb')) {
            return '';
        }

        return $this->getFirstMedia('thumb')->getPath();
    }

    public function getMealtimeAttribute()
    {
        if (!$this->menu_mealtime) {
            return '';
        }

        return $this->menu_mealtime->mealtime_name;
    }
}