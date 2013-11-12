<?php namespace Slender\Api\Facade;

use \Illuminate\Support\Facades\Facade as LaravelFacade;

class RouteCreator extends LaravelFacade {

    /**
     * Get the registered name of the component.
     *
     * @return string
     */
    protected static function getFacadeAccessor() { return 'route-creator'; }

}