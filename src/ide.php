<?php

namespace Illuminate\Routing {
    /**
     * @method void singleActionResources(array $resources, array $options = [])
     * @method \Illuminate\Routing\PendingResourceRegistration singleActionResource(string $name, ?string $controller = null, array $options = [])
     * @mixin \CleanBandits\SingleActionResourceControllers\Router
     */
    class Router
    {
        //
    }
}

namespace Illuminate\Support\Facades {
    /**
     * @method static void singleActionResources(array $resources, array $options = [])
     * @method static \Illuminate\Routing\PendingResourceRegistration singleActionResource(string $name, ?string $controller = null, array $options = [])
     * @mixin \CleanBandits\SingleActionResourceControllers\Router
     * @see \Illuminate\Routing\Router
     */
    class Route extends Facade
    {
        //
    }
}

namespace CleanBandits\SingleActionResourceControllers {
    use Closure;
    use Illuminate\Routing\PendingResourceRegistration;

    /**
     * @property-read Closure|\Illuminate\Routing\PendingResourceRegistration $singleActionResource
     * @method PendingResourceRegistration singleActionResource(string $name, ?string $controller = null, array $options = [])
     * @method void singleActionResources(array $resources, array $options = [])
     */
    class Router
    {
        //
    }
}
