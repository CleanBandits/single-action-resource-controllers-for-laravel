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

test('namespace of a group nested inside another group', function (): void {
    // Prepare
    $resource = 'photos';
    $action = $this->actions[0];
    $namespace = $this->controllersNamespace . 'Workspace';
    $controllerClass = $namespace . '\\' . str($resource)->studly() . '\\' . $action . 'Controller';
    $this->instance($controllerClass, $this->controllerMock($controllerClass));
    /** @var Router $router */
    $router = app('router');
    // Act
    $router->middleware('auth')->group(function () use ($router, $namespace, $resource): void {
        $router->namespace($namespace)->group(function () use ($router, $resource): void {
            $router->singleActionResource($resource);
        });
    });

    $route = $router->getRoutes()->getByName($resource . '.' . strtolower($action));
    // Assert
    expect($route->getControllerClass())->toBe($controllerClass)
        ->and($route->middleware())->toBe(['auth']);
});

test('relative namespaces of nested groups are merged', function (): void {
    // Prepare
    $resource = 'photos';
    $action = $this->actions[0];
    $controllerClass = $this->controllersNamespace . 'Modules\\Bookkeeping\\' . str($resource)->studly() . '\\' . $action . 'Controller';
    $this->instance($controllerClass, $this->controllerMock($controllerClass));
    /** @var Router $router */
    $router = app('router');
    // Act
    $router->namespace($this->controllersNamespace . 'Modules')->group(function () use ($router, $resource): void {
        $router->namespace('Bookkeeping')->group(function () use ($router, $resource): void {
            $router->singleActionResource($resource);
        });
    });

    $route = $router->getRoutes()->getByName($resource . '.' . strtolower($action));
    // Assert
    expect($route->getControllerClass())->toBe($controllerClass);
});

test('route names and parameters follow the controller when names_from_controller is on', function (): void {
    // Prepare
    config(['single-action-resource-controllers.names_from_controller' => true]);
    $controllerClassBase = $this->controllersNamespace . 'FamilyMembers\\';
    foreach ($this->actions as $action) {
        $controllerClass = $controllerClassBase . $action . 'Controller';
        $this->instance($controllerClass, $this->controllerMock($controllerClass));
    }
    /** @var Router $router */
    $router = app('router');
    // Act
    $router->singleActionResource('gimenes-locekli', 'FamilyMembers');

    $routes = $router->getRoutes();
    // Assert
    expect($routes->getByName('family-members.index')->uri())->toBe('gimenes-locekli')
        ->and($routes->getByName('family-members.edit')->uri())->toBe('gimenes-locekli/{familyMember}/edit')
        ->and($routes->getByName('family-members.update')->getControllerClass())->toBe($controllerClassBase . 'UpdateController')
        ->and($routes->getByName('gimenes-locekli.index'))->toBeNull();
});

test('explicit names and parameters win over the ones from the controller', function (): void {
    // Prepare
    config(['single-action-resource-controllers.names_from_controller' => true]);
    $controllerClass = $this->controllersNamespace . 'FamilyMembers\\EditController';
    $this->instance($controllerClass, $this->controllerMock($controllerClass));
    /** @var Router $router */
    $router = app('router');
    // Act
    $router->singleActionResource('gimenes-locekli', 'FamilyMembers')
        ->names('relatives')
        ->parameters(['gimenes-locekli' => 'relative']);

    $routes = $router->getRoutes();
    // Assert
    expect($routes->getByName('relatives.edit')->uri())->toBe('gimenes-locekli/{relative}/edit');
});

test('route names and parameters follow the uri by default', function (): void {
    // Prepare
    $controllerClass = $this->controllersNamespace . 'FamilyMembers\\EditController';
    $this->instance($controllerClass, $this->controllerMock($controllerClass));
    /** @var Router $router */
    $router = app('router');
    // Act
    $router->singleActionResource('gimenes-locekli', 'FamilyMembers');

    $routes = $router->getRoutes();
    // Assert
    expect($routes->getByName('gimenes-locekli.edit')->uri())->toBe('gimenes-locekli/{gimenes_locekli}/edit')
        ->and($routes->getByName('family-members.edit'))->toBeNull();
});
