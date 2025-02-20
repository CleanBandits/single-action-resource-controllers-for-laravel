<?php

use CleanBandits\SingleActionResourceControllers\Router;

beforeEach(function (): void {
    $this->actions = ['Index', 'Create', 'Store', 'Show', 'Edit', 'Update', 'Destroy'];
    $this->controllersNamespace = config('single-action-resource-controllers.controllers_namespace');
});

test('all web routes are created', function (): void {
    $resources = ['photos', 'articles'];

    foreach ($resources as $resource) {
        $controllerClassBase = $this->controllersNamespace . str($resource)->studly() . '\\';
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
                ->and($route->getControllerClass())->toBe($this->controllersNamespace . str($resource)->studly() . '\\' . $action . 'Controller');
        }
    }
});

test('custom namespace', function (): void {
    // Prepare
    $resource = 'photos';
    $prefix = 'Admin';
    $action = $this->actions[0];
    $namespace = $this->controllersNamespace . $prefix;
    $controllerClassBase = $namespace . '\\' . str($resource)->studly() . '\\';
    $controllerClass = $controllerClassBase . $action . 'Controller';
    $controller = $this->controllerMock($controllerClass);
    $this->instance($controllerClass, $controller);
    /** @var Router $router */
    $router = app('router');
    // Act
    $router->namespace($namespace)->group(function () use ($router, $resource): void {
        $router->singleActionResource($resource);
    });

    $routes = $router->getRoutes();

    $route = $routes->getByName(collect([$resource, strtolower($action)])->join('.'));
    // Assert
    expect($route->getActionMethod())->toBe('__invoke')
        ->and($route->getControllerClass())->toBe($controllerClassBase . $action . 'Controller');
});

test('nested resources', function (): void {
    $resource = 'photos.comments';
    $nestedPath = collect(explode('.', $resource))->map(fn (string $resource) => str($resource)->studly())->join('\\');
    $controllerClassBase = $this->controllersNamespace . $nestedPath . '\\';
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
            ->and($route->getControllerClass())
            ->toBe($this->controllersNamespace . $nestedPath . '\\' . $action . 'Controller');
    }
});
