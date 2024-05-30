<?php

use CleanBandits\SingleActionResourceControllers\Router;

beforeEach(function (): void {
    $this->actions = ['Index', 'Create', 'Store', 'Show', 'Edit', 'Update', 'Destroy'];
});

test('all web routes are created', function (): void {
    $resources = ['photos', 'articles'];

    foreach ($resources as $resource) {
        $controllerClassBase = config('single-action-resource-controllers.controllers_namespace') . str($resource)->studly() . '\\';
        // Prepare
        $actions = $this->actions;
        foreach ($actions as $action) {
            $controllerClass = $controllerClassBase . $action . 'Controller';
            $controller = $this->controllerMock($controllerClass);
            $this->instance($controllerClass, $controller);
        }
    }

    /** @var Router $router */
    $router = app('router');
    // Act
    $router->singleActionResources($resources);

    $routes = $router->getRoutes();
    // Assert
    expect(count($routes))->toBe(count($this->actions) * count($resources));

    foreach ($resources as $resource) {
        foreach ($this->actions as $action) {
            $route = $routes->getByName(collect([$resource, strtolower($action)])->join('.'));
            // Assert
            expect($route->getActionMethod())->toBe('__invoke')
                ->and($route->getControllerClass())->toBe(config('single-action-resource-controllers.controllers_namespace') . str($resource)->studly() . '\\' . $action . 'Controller');
        }
    }
});

test('nested resources', function (): void {
    $resource = 'photos.comments';
    $nestedPath = collect(explode('.', $resource))->map(fn (string $resource) => str($resource)->studly())->join('\\');
    $controllerClassBase = config('single-action-resource-controllers.controllers_namespace') . $nestedPath . '\\';
    // Prepare
    foreach ($this->actions as $action) {
        $controllerClass = $controllerClassBase . $action . 'Controller';
        $controller = $this->controllerMock($controllerClass);
        $this->instance($controllerClass, $controller);
    }

    /** @var Router $router */
    $router = app('router');
    // Act
    $router->singleActionResource($resource);

    $routes = $router->getRoutes();
    // Assert
    expect(count($routes))->toBe(count($this->actions));

    foreach ($this->actions as $action) {
        $route = $routes->getByName(collect([$resource, strtolower($action)])->join('.'));
        // Assert
        expect($route->getActionMethod())->toBe('__invoke')
            ->and($route->getControllerClass())->toBe(config('single-action-resource-controllers.controllers_namespace') . $nestedPath . '\\' . $action . 'Controller');
    }
});
