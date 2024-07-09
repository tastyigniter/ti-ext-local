<?php

namespace Igniter\Local\Http\Requests;

use Igniter\System\Classes\FormRequest;

class LocationRequest extends FormRequest
{
    public function attributes()
    {
        return [
            'location_name' => lang('igniter::admin.label_name'),
            'location_email' => lang('igniter::admin.label_email'),
            'location_telephone' => lang('igniter.local::default.label_telephone'),
            'location_address_1' => lang('igniter.local::default.label_address_1'),
            'location_address_2' => lang('igniter.local::default.label_address_2'),
            'location_city' => lang('igniter.local::default.label_city'),
            'location_state' => lang('igniter.local::default.label_state'),
            'location_postcode' => lang('igniter.local::default.label_postcode'),
            'options.auto_lat_lng' => lang('igniter.local::default.label_auto_lat_lng'),
            'location_lat' => lang('igniter.local::default.label_latitude'),
            'location_lng' => lang('igniter.local::default.label_longitude'),
            'description' => lang('igniter::admin.label_description'),
            'location_status' => lang('igniter::admin.label_status'),
            'permalink_slug' => lang('igniter.local::default.label_permalink_slug'),
            'gallery.title' => lang('igniter.local::default.label_gallery_title'),
            'gallery.description' => lang('igniter::admin.label_description'),
        ];
    }

    public function rules()
    {
        return [
            'location_name' => ['required', 'string', 'between:2,32'],
            'permalink_slug' => ['nullable', 'alpha_dash', 'max:255'],
            'location_email' => ['required', 'email:filter', 'max:96'],
            'location_telephone' => ['nullable', 'string'],
            'location_address_1' => ['required', 'string', 'between:2,255'],
            'location_address_2' => ['nullable', 'string', 'max:255'],
            'location_city' => ['nullable', 'string', 'max:255'],
            'location_state' => ['nullable', 'string', 'max:255'],
            'location_postcode' => ['nullable', 'string', 'max:15'],
            'is_auto_lat_lng' => ['required', 'boolean'],
            'location_lat' => ['required_if:is_auto_lat_lng,0', 'numeric'],
            'location_lng' => ['required_if:is_auto_lat_lng,0', 'numeric'],
            'description' => ['max:3028'],
            'location_status' => ['boolean'],
            'is_default' => ['boolean'],
        ];
    }
}
