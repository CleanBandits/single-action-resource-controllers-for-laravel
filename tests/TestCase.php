<?php

declare(strict_types=1);

namespace CleanBandits\SingleActionResourceControllers\Tests;

use CleanBandits\SingleActionResourceControllers\SingleActionResourceControllersProvider;
use CleanBandits\SingleActionResourceControllers\Tests\Unit\Mocks\SingleActionResourceControllers;
use Mockery;
use Orchestra\Testbench\TestCase as BaseTestCase;

abstract class TestCase extends BaseTestCase
{
    public function controllerMock(string $controllerClass): Mockery\LegacyMockInterface|(Mockery\MockInterface&SingleActionResourceControllers)
    {
        return Mockery::namedMock($controllerClass, SingleActionResourceControllers::class)->makePartial();
    }

    protected function getPackageProviders($app): array
    {
        return [
            SingleActionResourceControllersProvider::class,
        ];
    }

    protected function getEnvironmentSetUp($app): void
    {
        $app['config']->set('app.debug', true);
        $app['config']->set('single-action-resource-controllers.controllers_namespace', 'CleanBandits\\SingleActionResourceControllers\\Tests\\Unit\\Data\\Controllers\\');
    }
}
