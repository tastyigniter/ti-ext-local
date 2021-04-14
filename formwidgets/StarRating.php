<?php

namespace Igniter\Local\FormWidgets;

use Admin\Classes\BaseFormWidget;
use Igniter\Local\Models\Reviews_model;

/**
 * Star Rating
 * Renders a raty star field.
 */
class StarRating extends BaseFormWidget
{
    /**
     * @var array Default available hints
     */
    public static $hints = [];

    protected $defaultAlias = 'starrating';

    public function initialize()
    {
        $this->fillFromConfig([
            'hints',
        ]);

        if (!self::$hints) {
            self::$hints = $this->getHints();
        }
    }

    public function render()
    {
        $this->prepareVars();

        return $this->makePartial('starrating/starrating');
    }

    /**
     * Prepares the list data
     */
    public function prepareVars()
    {
        $this->vars['field'] = $this->formField;
        $this->vars['name'] = $this->formField->getName();
        $this->vars['value'] = $value = $this->getLoadValue();
        $this->vars['hints'] = array_values(self::$hints);
    }

    public function loadAssets()
    {
        $this->addCss('vendor/raty/jquery.raty.css', 'jquery-raty-css');
        $this->addJs('vendor/raty/jquery.raty.js', 'jquery-raty-js');

        $this->addCss('css/starrating.css', 'starrating-css');
        $this->addJs('js/starrating.js', 'starrating-js');
    }

    public function getSaveValue($value)
    {
        return $value ? (int)$value : 0;
    }

    /**
     * @return mixed
     */
    protected function getHints()
    {
        return Reviews_model::make()->getRatingOptions();
    }
}
