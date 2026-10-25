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

    /*
     * Take route names and route parameters from the controller name instead of the URI.
     * Useful when URIs are not in English:
     * Route::singleActionResource('gimenes-locekli', 'FamilyMembers') gets family-members.* route names
     * and a {familyMember} parameter. Names and parameters passed explicitly still win.
     */
    'names_from_controller' => false,
];
