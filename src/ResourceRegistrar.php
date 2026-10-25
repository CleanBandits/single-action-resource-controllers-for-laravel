<?php

namespace CleanBandits\SingleActionResourceControllers;

use Illuminate\Routing\RouteCollection;

class ResourceRegistrar extends \Illuminate\Routing\ResourceRegistrar
{
    public function register($name, $controller, array $options = []): RouteCollection
    {
        if (config('single-action-resource-controllers.names_from_controller')) {
            $options = $this->withNamesFromController($name, $controller, $options);
        }

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
        $namespace = last($this->router->getGroupStack())['namespace'] ?? null;

        $collection = new RouteCollection();
        $resourceController = app(ResourceController::class);
        $resourceMethods = collect($this->getResourceMethods($defaults, $options))
            ->filter(fn (string $action) => class_exists($resourceController->namespace($controller, $action, $namespace)));
        $resourceMethods->each(function (string $action) use ($name, $base, $controller, $options, $collection, $resourceMethods, $resourceController, $namespace): void {
            $route = $this->{'addResource' . ucfirst($action)}(
                $name, $base, $resourceController->namespace($controller, $action, $namespace), $options
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

    /**
     * Route names and parameters follow the controller name instead of the URI,
     * e.g. ('gimenes-locekli', 'FamilyMembers') gets family-members.* names and a {familyMember} parameter.
     * Names and parameters passed explicitly still win.
     */
    protected function withNamesFromController(string $name, string $controller, array $options): array
    {
        $resources = explode('.', $name);
        $controllers = explode('.', $controller);

        $options['names'] ??= collect($controllers)->map(fn (string $segment) => (string) str($segment)->kebab())->join('.');

        if (count($resources) !== count($controllers) || (isset($options['parameters']) && !is_array($options['parameters']))) {
            return $options;
        }

        $parameters = collect($resources)
            ->combine(collect($controllers)->map(fn (string $segment) => (string) str($segment)->singular()->camel()))
            ->all();
        $options['parameters'] = ($options['parameters'] ?? []) + $parameters;

        return $options;
    }
}
