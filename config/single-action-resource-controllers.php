<?php

use CleanBandits\SingleActionResourceControllers\DefaultResourceController;

return [
    // Specify root namespace where your single action resource controller folder will reside
    'controllers_namespace' => 'App\\Http\\Controllers\\',

    /*
     * This class is responsible for building Resource controllers naming and location.
     * By default they reside inside namespace -> controllers_namespace+resource_name+action,
     * e.g. App\Http\Controllers\Photos\IndexController
     * To take full control of controller namespace creation,
     * you can provide your own class that implements ResourceController
     */
    'resource_controller' => DefaultResourceController::class,
];
