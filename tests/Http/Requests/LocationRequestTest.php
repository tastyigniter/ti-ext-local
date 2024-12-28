<?php

namespace Igniter\Local\Tests\Http\Requests;

use Igniter\Local\Http\Requests\LocationRequest;

it('returns correct attribute labels', function() {
    $attributes = (new LocationRequest())->attributes();

    expect($attributes)->toHaveKey('location_name', lang('igniter::admin.label_name'))
        ->and($attributes)->toHaveKey('location_email', lang('igniter::admin.label_email'))
        ->and($attributes)->toHaveKey('location_telephone', lang('igniter.local::default.label_telephone'))
        ->and($attributes)->toHaveKey('location_address_1', lang('igniter.local::default.label_address_1'))
        ->and($attributes)->toHaveKey('location_address_2', lang('igniter.local::default.label_address_2'))
        ->and($attributes)->toHaveKey('location_city', lang('igniter.local::default.label_city'))
        ->and($attributes)->toHaveKey('location_state', lang('igniter.local::default.label_state'))
        ->and($attributes)->toHaveKey('location_postcode', lang('igniter.local::default.label_postcode'))
        ->and($attributes)->toHaveKey('options.auto_lat_lng', lang('igniter.local::default.label_auto_lat_lng'))
        ->and($attributes)->toHaveKey('location_lat', lang('igniter.local::default.label_latitude'))
        ->and($attributes)->toHaveKey('location_lng', lang('igniter.local::default.label_longitude'))
        ->and($attributes)->toHaveKey('description', lang('igniter::admin.label_description'))
        ->and($attributes)->toHaveKey('location_status', lang('igniter::admin.label_status'))
        ->and($attributes)->toHaveKey('permalink_slug', lang('igniter.local::default.label_permalink_slug'))
        ->and($attributes)->toHaveKey('gallery.title', lang('igniter.local::default.label_gallery_title'))
        ->and($attributes)->toHaveKey('gallery.description', lang('igniter::admin.label_description'));
});

it('returns correct validation rules', function() {
    $rules = (new LocationRequest())->rules();

    expect($rules)->toHaveKey('location_name', ['required', 'string', 'between:2,32'])
        ->and($rules)->toHaveKey('permalink_slug', ['nullable', 'alpha_dash', 'max:255'])
        ->and($rules)->toHaveKey('location_email', ['required', 'email:filter', 'max:96'])
        ->and($rules)->toHaveKey('location_telephone', ['nullable', 'string'])
        ->and($rules)->toHaveKey('location_address_1', ['required', 'string', 'between:2,255'])
        ->and($rules)->toHaveKey('location_address_2', ['nullable', 'string', 'max:255'])
        ->and($rules)->toHaveKey('location_city', ['nullable', 'string', 'max:255'])
        ->and($rules)->toHaveKey('location_state', ['nullable', 'string', 'max:255'])
        ->and($rules)->toHaveKey('location_postcode', ['nullable', 'string', 'max:15'])
        ->and($rules)->toHaveKey('is_auto_lat_lng', ['required', 'boolean'])
        ->and($rules)->toHaveKey('location_lat', ['required_if:is_auto_lat_lng,0', 'numeric'])
        ->and($rules)->toHaveKey('location_lng', ['required_if:is_auto_lat_lng,0', 'numeric'])
        ->and($rules)->toHaveKey('description', ['max:3028'])
        ->and($rules)->toHaveKey('location_status', ['boolean'])
        ->and($rules)->toHaveKey('is_default', ['boolean']);
});
