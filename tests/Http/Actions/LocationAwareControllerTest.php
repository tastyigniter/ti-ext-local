<?php

namespace Igniter\Local\Tests\Http\Actions;

use Igniter\Admin\Classes\AdminController;
use Igniter\Admin\Http\Actions\FormController;
use Igniter\Admin\Http\Actions\ListController;
use Igniter\Cart\Models\Menu;
use Igniter\Local\Facades\Location as LocationFacade;
use Igniter\Local\Http\Actions\LocationAwareController;
use Igniter\Local\Models\Location;
use Igniter\Local\Models\Review;
use Illuminate\Support\Facades\Event;

beforeEach(function() {
    Event::fake();

    $this->controller = new class extends AdminController
    {
        public array $implement = [
            ListController::class,
            FormController::class,
            LocationAwareController::class,
        ];

        public $locationConfig = [
            'applyScopeOnListQuery' => true,
            'applyScopeOnFormQuery' => true,
            'addAbsenceConstraint' => false,
        ];

        public $listConfig = [
            'list' => [
                'model' => Location::class,
                'configFile' => 'config_file',
            ],
        ];

        public $formConfig = [
            'model' => Location::class,
            'configFile' => 'config_file',
        ];
    };

    $this->locationAwareController = new LocationAwareController($this->controller);
});

it('initializes correctly', function() {
    expect($this->locationAwareController->locationConfig)->toBe($this->controller->locationConfig)
        ->and($this->controller->hiddenActions)->toContain('locationApplyScope');
});

it('binds location events', function() {
    $locationAwareControllerMock = $this->getMockBuilder(LocationAwareController::class)
        ->setConstructorArgs([$this->controller])
        ->onlyMethods(['locationBindEvents'])
        ->getMock();

    $locationAwareControllerMock->expects($this->once())->method('locationBindEvents');

    $this->controller->fireEvent('controller.beforeRemap');
});

it('applies location scope correctly', function($query, $expectedSql) {
    LocationFacade::shouldReceive('currentOrAssigned')->andReturn([1, 2]);
    $this->locationAwareController->locationApplyScope($query);

    expect($query->toSql())->toContain($expectedSql);
})->with([
    fn() => [Review::query(), '`location_id` in (?, ?)'],
    fn() => [Menu::query(), 'and `locationables`.`location_id` in (?, ?)'],
]);

it('does not applies location scope when user current or assigned location is missing', function() {
    $query = Review::query();

    LocationFacade::shouldReceive('currentOrAssigned')->andReturn([]);
    $this->locationAwareController->locationApplyScope($query);

    expect($query->toSql())->not->toContain('`location_id`');
});

it('applies absence location scope correctly', function($query, $expectedSql) {
    $this->locationAwareController->setConfig([
        'addAbsenceConstraint' => true,
    ]);
    LocationFacade::shouldReceive('currentOrAssigned')->andReturn([1, 2]);

    $this->locationAwareController->locationApplyScope($query);

    expect($query->toSql())->toContain($expectedSql);
})->with([
    fn() => [Review::query(), 'or `location_id` is null'],
    fn() => [Menu::query(), ' or not exists (select * from `locations`'],
]);
