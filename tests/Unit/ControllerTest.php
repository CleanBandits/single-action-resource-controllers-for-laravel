<?php

beforeEach(function (): void {
    $this->resource = 'photos';
    $this->controllerClassBase = config('single-action-resource-controllers.controllers_namespace') . 'Photos\\';
});

test('index controller', function (): void {
    // Prepare
    $controllerClass = $this->controllerClassBase . 'IndexController';
    $index = $this->controllerMock($controllerClass);
    $index
        ->shouldReceive('__invoke')
        ->once()
        ->andReturn(response('index'));
    $this->instance($controllerClass, $index);

    // Set Route
    app('router')->singleActionResource($this->resource);

    // Act
    $response = $this->get($this->resource);

    // Assert
    expect($response->status())->toBe(200)
        ->and($response->getContent())->toBe('index');
});

test('create controller', function (): void {
    // Prepare
    $controllerClass = $this->controllerClassBase . 'CreateController';
    $create = $this->controllerMock($controllerClass);
    $create
        ->shouldReceive('__invoke')
        ->once()
        ->andReturn(response('create'));
    $this->instance($controllerClass, $create);

    // Set Route
    app('router')->singleActionResource($this->resource);

    // Act
    $response = $this->get("/{$this->resource}/create");

    // Assert
    expect($response->status())->toBe(200)
        ->and($response->getContent())->toBe('create');
});

test('store controller', function (): void {
    // Prepare
    $controllerClass = $this->controllerClassBase . 'StoreController';
    $title = fake()->word();
    $show = $this->controllerMock($controllerClass);
    $show
        ->shouldReceive('__invoke')
        ->once()
        ->andReturnUsing(function () {
            return 'post:' . request()->get('title');
        });
    $this->instance($controllerClass, $show);

    // Set Route
    app('router')->singleActionResource($this->resource);

    // Act
    $response = $this->json('post', $this->resource, ['title' => $title]);

    // Assert
    expect($response->status())->toBe(200)
        ->and($response->getContent())->toBe('post:' . $title);
});

test('show controller', function (): void {
    // Prepare
    $controllerClass = $this->controllerClassBase . 'ShowController';
    $photoId = fake()->numberBetween();
    $show = $this->controllerMock($controllerClass);
    $show
        ->shouldReceive('__invoke')
        ->once()
        ->andReturnUsing(fn (int $id) => 'show:' . $photoId);
    $this->instance($controllerClass, $show);

    // Set Route
    app('router')->singleActionResource($this->resource);

    // Act
    $response = $this->get($this->resource . '/' . $photoId);

    // Assert
    expect($response->status())->toBe(200)
        ->and($response->getContent())->toBe('show:' . $photoId);
});

test('edit controller', function (): void {
    // Prepare
    $controllerClass = $this->controllerClassBase . 'EditController';
    $photoId = fake()->numberBetween();
    $show = $this->controllerMock($controllerClass);
    $show
        ->shouldReceive('__invoke')
        ->once()
        ->andReturnUsing(fn (int $id) => 'edit:' . $photoId);
    $this->instance($controllerClass, $show);

    // Set Route
    app('router')->singleActionResource($this->resource);

    // Act
    $response = $this->get($this->resource . '/' . $photoId . '/edit');

    // Assert
    expect($response->status())->toBe(200)
        ->and($response->getContent())->toBe('edit:' . $photoId);
});

test('update controller', function (): void {
    // Prepare
    $controllerClass = $this->controllerClassBase . 'UpdateController';
    $title = fake()->word();
    $photoId = fake()->numberBetween();
    $show = $this->controllerMock($controllerClass);
    $show
        ->shouldReceive('__invoke')
        ->twice()
        ->andReturnUsing(function (int $id) {
            return collect(['update', $id, request()->get('title')])->join(':');
        });
    $this->instance($controllerClass, $show);

    // Set Route
    app('router')->singleActionResource($this->resource);

    foreach (['patch', 'put'] as $action) {
        // Act
        $response = $this->json($action, $this->resource . '/' . $photoId, ['title' => $title]);

        // Assert
        expect($response->status())->toBe(200)
            ->and($response->getContent())->toBe(collect(['update', $photoId, $title])->join(':'));
    }
});

test('destroy controller', function (): void {
    // Prepare
    $photoId = fake()->numberBetween();
    $controllerClass = $this->controllerClassBase . 'DestroyController';
    $destroy = $this->controllerMock($controllerClass);
    $destroy
        ->shouldReceive('__invoke')
        ->once()
        ->andReturnUsing(fn (int $id) => 'destroy:' . $photoId);

    $this->instance($controllerClass, $destroy);

    // Set Route
    app('router')->singleActionResource($this->resource);

    // Act
    $response = $this->delete($this->resource . '/' . $photoId);

    // Assert
    expect($response->status())->toBe(200)
        ->and($response->getContent())->toBe('destroy:' . $photoId);
});
