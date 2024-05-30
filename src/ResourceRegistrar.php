<?php

namespace CleanBandits\SingleActionResourceControllers;

use Illuminate\Routing\RouteCollection;

class ResourceRegistrar extends \Illuminate\Routing\ResourceRegistrar
{
    public function register($name, $controller, array $options = []): RouteCollection
    {
        if (isset($options['parameters']) && !isset($this->parameters)) {
            $this->parameters = $options['parameters'];
        }

        // If the resource name contains a slash, we will assume the developer wishes to
        // register these resource routes with a prefix so we will set that up out of
        // the box so they don't have to mess with it. Otherwise, we will continue.
        if (str_contains($name, '/')) {
            $this->prefixedResource($name, $controller, $options);

            return new RouteCollection();
        }

        // We need to extract the base resource from the resource name. Nested resources
        // are supported in the framework, but we need to know what name to use for a
        // place-holder on the route parameters, which should be the base resources.
        $base = $this->getResourceWildcard(last(explode('.', $name)));

        $defaults = $this->resourceDefaults;

        $collection = new RouteCollection();
        $resourceController = app(ResourceController::class);
        $resourceMethods = collect($this->getResourceMethods($defaults, $options))
            ->filter(fn (string $action) => class_exists($resourceController->namespace($controller, $action)));
        $resourceMethods->each(function (string $action) use ($name, $base, $controller, $options, $collection, $resourceMethods, $resourceController): void {
            $route = $this->{'addResource' . ucfirst($action)}(
                $name, $base, $resourceController->namespace($controller, $action), $options
            );

            if (isset($options['bindingFields'])) {
                $this->setResourceBindingFields($route, $options['bindingFields']);
            }

            if (isset($options['trashed'])
                && in_array($action, !empty($options['trashed']) ? $options['trashed'] : $resourceMethods->intersect(['show', 'edit', 'update'])->all())) {
                $route->withTrashed();
            }

            $collection->add($route);
        });

        return $collection;
    }

    protected function getResourceAction($resource, $controller, $method, $options): array
    {
        $action = parent::getResourceAction($resource, $controller, $method, $options);
        $action['uses'] = preg_replace('/@(.*)/m', '@__invoke', $action['uses']);

        return $action;
    }
}
