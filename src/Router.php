<?php

namespace CleanBandits\SingleActionResourceControllers;

use Illuminate\Routing\PendingResourceRegistration;
use Illuminate\Routing\Router as DefaultRouter;

class Router extends DefaultRouter
{
    public function singleActionResources(array $resources, array $options = []): void
    {
        foreach ($resources as $name => $controller) {
            if (is_int($name)) {
                $this->singleActionResource(name: $controller, options: $options);
            } else {
                $this->singleActionResource($name, $controller, $options);
            }
        }
    }

    public function singleActionResource(string $name, ?string $controller = null, array $options = []): PendingResourceRegistration
    {
        if ($this->container && $this->container->bound(ResourceRegistrar::class)) {
            $registrar = $this->container->make(ResourceRegistrar::class);
        } else {
            $registrar = new ResourceRegistrar($this);
        }

        return new PendingResourceRegistration(
            $registrar, $name, $this->controller($name, $controller), $options
        );
    }

    private function controller(string $name, ?string $controller): string
    {
        return $controller ?? str($name)->studly();
    }
}
