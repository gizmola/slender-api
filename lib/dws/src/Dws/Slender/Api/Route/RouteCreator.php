<?php

namespace Dws\Slender\Api\Route;

use Illuminate\Foundation\Application;
use LMongo\LMongoManager;

/**
 * An object to create REST routes for site-based resources
 *
 * @author David Weinraub <david.weinraub@diamondwebservices.com>
 */
class RouteCreator
{
	/**
	 * @var Application
	 */
	protected $app;
    
	/**
	 * A mapping of HTTP request methods to controller methods
	 * 
	 * @var array
	 */
	protected $methodMap = [
		'getSingular' => 'view',
		'getPlural' => 'index',
		'putSingular' => 'update',
		'postPlural' => 'insert',
		'deleteSingular' => 'delete',
		'options' => 'options',
	];
    
    /**
     *
     * @var \LMongoManager
     */
    protected $mongoManager;
	
	/**
	 * Constructor
	 * 
	 * @param \Illuminate\Foundation\Application $app
	 */
    public function __construct(Application $app, LMongoManager $mongoManager = null)
    {
		$this->app = $app;
        if ($mongoManager){
            $this->setMongoManager($mongoManager);
        }
    }
	
	/**
	 * @var array $config
	 * 
	 * $config format is something like:
	 * 
	 * [
	 *     'resources' => [
	 * 
	 *         'photos' => [
	 *			   
	 *             // Optional, but defaults to false
	 *			   // The idea is that you can still keep it in the system
	 *			   // but disable it globally
	 *             'isEnabled' => true,
     *
	 *     ],
	 * 
	 *     'sites' => [
	 * 
	 *         'site1' => [
	 * 
	 *             'photos' => [
	 * 
	 *                 // Optional, but defaults to false
	 *                 // The idea here is that you can leave the config in the
     *                 // in the system but disable it locally
	 *                 'isEnabled': true,
     * 
	 *         ],
	 *     ],
	 * ];
	 * 
	 * 
	 */
	public function addSiteRoutes(array $config)
	{
        $config = new SitesConfig($config);
        
		$singularUrl = '{site}/{resource}/{id}';
		$singularCallback = $this->buildSiteSingularCallback($config);
		
		$pluralUrl = '{site}/{resource}';
		$pluralCallback = $this->buildSitePluralCallback($config);
        
        $this->addRoutesToRouter($this->app['router'], 
                $singularUrl, $singularCallback, 
                $pluralUrl, $pluralCallback);
	}
    
    protected function addRoutesToRouter($router, $singularUrl, $singularCallback, $pluralUrl, $pluralCallback)
    {
        // GET routes
		$router->get($singularUrl, $singularCallback);
		$router->get($pluralUrl, $pluralCallback);
		
		// PUT routes
		$router->put($singularUrl, $singularCallback);

		// POST routes
		$router->post($pluralUrl, $pluralCallback);

		// DELETE routes
		$router->delete($singularUrl, $singularCallback);

		// OPTIONS routes
		$router->match('options', $pluralUrl, $pluralCallback);        
    }
	
	protected function buildSitePluralCallback(SitesConfig $config)
	{
		$creator = $this;		
		$callback = function($site, $resource) use ($creator, $config) {
            $controller = $creator->buildSiteController($site, $resource, $config);             
            $method = $creator->buildControllerMethod('plural');
            return $controller->$method();
		};		
		return $callback;		
	}
	
	public function buildSiteSingularCallback(SitesConfig $config)
	{
        $creator = $this;
		$callback = function($site, $resource, $id) use ($creator, $config) {
            $controller = $creator->buildSiteController($site, $resource, $config);
            $method = $creator->buildControllerMethod('singular');
			return $controller->$method($id);
		};		
		return $callback;		
	}
    
    public function buildSiteController($site, $resource, SitesConfig $config)
    {
        $modelClass = $config->getModelClass($site, $resource);
        if (!$modelClass) {
            $msg = sprintf('Unable to resolve model for resource %s and site %s',
                        $resource, $site);
            throw new RouteException($msg);
        }
        
        $controllerClass = $config->getControllerClass($site, $resource);
        if (!$controllerClass) {
            $msg = sprintf('Unable to resolve controller for resource %s and site %s',
                        $resource, $site);
            throw new RouteException($msg);
        }
        
        $connection = $this->getMongoManager()->connection($site);
        if (!$connection) {
            throw new RouteException();
        }

        $modelInstance = new $modelClass($connection);
        
        $controller = new $controllerClass($modelInstance);
        
        return $controller;
    }
    
    /**
     * 
     * @param string $type Values: 'singular' or 'plural'
     * @return string 
     */
    public function buildControllerMethod($type)
    {
        $httpMethod = $this->app['request']->getMethod();
        $method = $this->getMappedMethod($type, $httpMethod);
        return $method;                
    }
    
    /**
     * Get a controller method name given a type and http method
     * 
     * @param string $type  Values are 'singular' or 'plural'
     * @param string $httpMethod The HTTP method of the requesdt
     * @return string the name of the corresponding controller method 
     * @throws RouteException
     */
	public function getMappedMethod($type, $httpMethod)
	{
		$type = strtolower($type);        
        if (!in_array($type, array('singular', 'plural'))) {
			throw new RouteException('Invalid plural/singular parameter: ' . $type);
		}
		return $this->methodMap[strtolower($httpMethod) . ucfirst($type)];
	}
    
    /**
     * Add all core routes from config
     */
    public function addCoreRoutes()
    {
        $coreResources = $this->app['config']['app.core-resources'];
        $connection = $this->getMongoManager()->connection('default');
        foreach ($coreResources as $resource) {
            $this->addCoreRoute($resource, $connection);
        }
    }
    
    /**
     * Add a single core route with the given resource name
     * 
     * @param string $resource
     * @param LMongo\MongoManager $connection
     * @return Creator
     */
    public function addCoreRoute($resource, $connection)
    {
        $creator = $this;
        
        $pluralUrl = $resource;
        $pluralCallback = function() use ($connection, $resource, $creator) {
            
            $modelClass = ucfirst($resource);            
            $model = new $modelClass($connection);
            
            $controllerClass = ucfirst($resource) . 'Controller';
            $controller = new $controllerClass($model);
            
            $method = $creator->buildControllerMethod('plural');
            
            return $controller->$method();
        };
        
        $singularUrl = $resource . '/{id}';
        $singularCallback = function($id) use ($connection, $resource, $creator) {
                        
            $modelClass = ucfirst($resource);
            $model = new $modelClass($connection);
            
            $controllerClass = ucfirst($resource) . 'Controller';
            $controller = new $controllerClass($model);
            
            $method = $creator->buildControllerMethod('singular');
            
            return $controller->$method($id);
        };
        
        $this->addRoutesToRouter($this->app['router'], $singularUrl, $singularCallback, $pluralUrl, $pluralCallback);
        
        return $this;
    }
    
    /**
     * Get the MongoDB manager
     * 
     * @return \LMongo\LMongoManager
     */
    public function getMongoManager()
    {
        if (null == $this->mongoManager) {
            $this->mongoManager = $this->app->make('mongo');
        }
        return $this->mongoManager;
    }

    /**
     * Sets the MongoDb manager
     * 
     * @param \LMongo\LMongoManager $mongoManager
     * @return \Dws\Slender\Api\Route\RouteCreator
     */
    public function setMongoManager(LMongoManager $mongoManager)
    {
        $this->mongoManager = $mongoManager;
        return $this;
    }
}
