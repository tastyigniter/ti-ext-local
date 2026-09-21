<?php

declare(strict_types=1);

namespace Igniter\Local\Tests\Http\Requests;

use Igniter\Local\Http\Requests\ReviewRequest;

it('returns correct attribute labels', function(): void {
    $attributes = (new ReviewRequest)->attributes();

    expect($attributes)->toHaveKey('reviewable_type', lang('igniter.local::default.reviews.label_reviewable_type'))
        ->and($attributes)->toHaveKey('reviewable_id', lang('igniter.local::default.reviews.label_reviewable_id'))
        ->and($attributes)->toHaveKey('location_id', lang('igniter.local::default.reviews.label_location'))
        ->and($attributes)->toHaveKey('author', lang('igniter.local::default.reviews.label_customer'))
        ->and($attributes)->toHaveKey('quality', lang('igniter.local::default.reviews.label_quality'))
        ->and($attributes)->toHaveKey('delivery', lang('igniter.local::default.reviews.label_delivery'))
        ->and($attributes)->toHaveKey('service', lang('igniter.local::default.reviews.label_service'))
        ->and($attributes)->toHaveKey('review_text', lang('igniter.local::default.reviews.label_text'))
        ->and($attributes)->toHaveKey('review_status', lang('admin::lang.label_status'));
});

it('returns correct validation rules', function(): void {
    $reviewRequest = new ReviewRequest;
    $reviewRequest->merge(['reviewable_type' => 'orders']);

    $rules = $reviewRequest->rules();

    expect($rules)->toHaveKey('reviewable_type', ['required', 'in:orders,reservations'])
        ->and($rules)->toHaveKey('reviewable_id', ['required', 'integer', 'exists:orders,order_id'])
        ->and($rules)->toHaveKey('location_id', ['required', 'integer'])
        ->and($rules)->toHaveKey('author', ['sometimes', 'required', 'string', 'between:2,255'])
        ->and($rules)->toHaveKey('quality', ['required', 'integer', 'min:1', 'max:5'])
        ->and($rules)->toHaveKey('delivery', ['required', 'integer', 'min:1', 'max:5'])
        ->and($rules)->toHaveKey('service', ['required', 'integer', 'min:1', 'max:5'])
        ->and($rules)->toHaveKey('review_text', ['required', 'between:2,1028'])
        ->and($rules)->toHaveKey('review_status', ['required', 'boolean']);
});

it('builds exists rules from the reviewable model mapping', function(): void {
    $reviewRequest = new ReviewRequest;
    $reviewRequest->merge(['reviewable_type' => 'reservations']);

    expect($reviewRequest->rules()['reviewable_id'])->toBe(['required', 'integer', 'exists:reservations,reservation_id']);
});

it('does not interpolate unallowlisted reviewable types into exists rules', function(): void {
    $reviewRequest = new ReviewRequest;
    $reviewRequest->merge(['reviewable_type' => 'users']);

    $rules = $reviewRequest->rules();

    expect($rules['reviewable_type'])->toBe(['required', 'in:orders,reservations'])
        ->and($rules['reviewable_id'])->toBe(['required', 'integer']);
});

it('does not interpolate non-string reviewable types into exists rules', function(): void {
    $reviewRequest = new ReviewRequest;
    $reviewRequest->merge(['reviewable_type' => ['users']]);

    expect($reviewRequest->rules()['reviewable_id'])->toBe(['required', 'integer']);
});
