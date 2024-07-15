<?php

namespace CleanBandits\SingleActionResourceControllers;

use Closure;
use Illuminate\Routing\PendingResourceRegistration;

class Router
{
    public function singleActionResources(): Closure
    {
        return function (array $resources, array $options = []): void {
            foreach ($resources as $name => $controller) {
                if (is_int($name)) {
                    $this->singleActionResource(name: $controller, options: $options);
                } else {
                    $this->singleActionResource($name, $controller, $options);
                }
            }
        };
    }

    public function singleActionResource(): Closure
    {
        return function (string $name, ?string $controller = null, array $options = []): PendingResourceRegistration {
            if ($this->container && $this->container->bound(ResourceRegistrar::class)) {
                $registrar = $this->container->make(ResourceRegistrar::class);
            } else {
                $registrar = new ResourceRegistrar($this);
            }

            return new PendingResourceRegistration(
                $registrar, $name, $this->controller($name, $controller), $options
            );
        };
    }

    protected function controller(): Closure
    {
        return fn (string $name, ?string $controller): string => $controller ?? str($name)->studly();
    }
}
