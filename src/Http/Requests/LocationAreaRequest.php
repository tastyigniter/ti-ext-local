<?php

namespace Igniter\Local\Http\Requests;

use Igniter\System\Classes\FormRequest;

class LocationAreaRequest extends FormRequest
{
    public function attributes()
    {
        return [
            'type' => lang('igniter.local::default.label_area_type'),
            'name' => lang('igniter.local::default.label_area_name'),
            'area_id' => lang('igniter.local::default.label_area_id'),
            'boundaries.components' => lang('igniter.local::default.label_address_component'),
            'boundaries.components.*.type' => lang('igniter.local::default.label_address_component_type'),
            'boundaries.components.*.value' => lang('igniter.local::default.label_address_component_value'),
            'boundaries.polygon' => lang('igniter.local::default.label_area_shape'),
            'boundaries.circle' => lang('igniter.local::default.label_area_circle'),
            'boundaries.vertices' => lang('igniter.local::default.label_area_vertices'),
            'boundaries.distance.*.type' => lang('igniter.local::default.label_area_distance'),
            'boundaries.distance.*.distance' => lang('igniter.local::default.label_area_distance'),
            'boundaries.distance.*.charge' => lang('igniter.local::default.label_area_charge'),
            'conditions' => lang('igniter.local::default.label_delivery_condition'),
            'conditions.*.amount' => lang('igniter.local::default.label_area_charge'),
            'conditions.*.type' => lang('igniter.local::default.label_charge_condition'),
            'conditions.*.total' => lang('igniter.local::default.label_area_min_amount'),
        ];
    }

    public function rules()
    {
        return [
            'type' => ['sometimes', 'required', 'string'],
            'name' => ['sometimes', 'required', 'string'],
            'area_id' => ['integer'],
            'boundaries.components' => ['sometimes', 'required_if:type,address', 'array'],
            'boundaries.components.*.type' => ['sometimes', 'required', 'string'],
            'boundaries.components.*.value' => ['sometimes', 'required', 'string'],
            'boundaries.polygon' => ['sometimes', 'required_if:type,polygon'],
            'boundaries.circle' => ['nullable', 'required_if:type,circle', 'json'],
            'boundaries.vertices' => ['nullable', 'required_unless:type,address', 'json'],
            'boundaries.distance.*.type' => ['sometimes', 'required', 'string'],
            'boundaries.distance.*.distance' => ['sometimes', 'required', 'numeric'],
            'boundaries.distance.*.charge' => ['sometimes', 'required', 'numeric'],
            'conditions' => ['sometimes', 'array'],
            'conditions.*.amount' => ['sometimes', 'required', 'numeric'],
            'conditions.*.type' => ['sometimes', 'required', 'alpha_dash'],
            'conditions.*.total' => ['sometimes', 'required', 'numeric'],
        ];
    }
}
