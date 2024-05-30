<?php

namespace CleanBandits\SingleActionResourceControllers;

use Illuminate\Support\ServiceProvider;

class SingleActionResourceControllersProvider extends ServiceProvider
{
    public function boot(): void
    {
        $this->publishes([
            dirname(__DIR__) . '/config/single-action-resource-controllers.php' => config_path('single-action-resource-controllers.php'),
        ], 'config');
    }

    public function register(): void
    {
        $this->mergeConfigFrom(
            dirname(__DIR__) . '/config/single-action-resource-controllers.php', 'single-action-resource-controllers'
        );

        $this->app->bind(ResourceController::class, config('single-action-resource-controllers.resource_controller'));
        $this->app->singleton('router', Router::class);
    }
}
