<?php namespace Slender\Api\Route;

use Illuminate\Foundation\Application;

class RouteCreator {



    public function __construct(SlenderConfig $config)
    {
        $this->config = $config;
    }

    /**
     * Add all core routes from config
     */
    public function loadCoreRoutes()
    {


        //dd(\SlenderConfig::get('api::config'));

        
        $this->coreResources = array_get($this->app['config'], 'api::config.core-resources');

        //dd($this->config);
        
        try {

            $connection = $this->getMongoManager()->connection('default');
        
        } catch (\InvalidArgumentException $e) {
        
            throw new RouteException($e->getMessage());
        
        }
        
        foreach ($this->coreResources as $resource) {
        
            $this->addCoreRoute($resource, $connection);
        
        }
    
    }

}