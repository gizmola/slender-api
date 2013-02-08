<?php

namespace Dws\Slender\Api\Route;

use Illuminate\Foundation\Application;

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
	 * Constructor
	 * 
	 * @param \Illuminate\Foundation\Application $app
	 */
    public function __construct(Application $app)
    {
		$this->app = $app;
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
		$singularCallback = $this->buildSingularCallback($config);
		
		$pluralUrl = '{site}/{resource}';
		$pluralCallback = $this->buildPluralCallback($config);
        
        $this->addRoutesToRouter($this->app['router'], 
                $singularUrl, $singularCallback, 
                $pluralUrl, $pluralCallback);
	}
    
    protected function addRoutesToRouter($router, 
            $singularUrl, $singularCallback, 
            $pluralUrl, $pluralCallback)
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
	
//	protected function buildPluralUrl($site, $resource)
//	{
//		return $this->buildUrl($site, $resource, false);
//	}
//	
//	protected function buildSingularUrl($site, $resource)
//	{
//		return $this->buildUrl($site, $resource, true);
//	}
//	
//	protected function buildUrl($site, $resource, $isSingular)
//	{
//		$route = ($site ? $site . '/' : '') . $resource;
//		if ($isSingular) {
//			$route .= '/{id}';
//		}
//		return $route;	
//	}
//	
	protected function buildPluralCallback(SitesConfig $config)
	{
		$creator = $this;		
		$callback = function($site, $resource) use ($creator, $config) {
            $controller = $creator->buildController($site, $resource, $creator, $config);
            die("<p>Debug :: " . __FILE__ . "(" . __LINE__ . ") :: " . __FUNCTION__ . " :: message</p>");
            $method = $creator->buildMethod('plural');
            return $controller->$method();
		};		
		return $callback;		
	}
	
	public function buildSingularCallback(SitesConfig $config)
	{
        $creator = $this;
		$callback = function($site, $resource, $id) use ($config) {
            $controller = $creator->buildController($site, $resource, $config);
            $method = $creator->buildMethod('singular');
			return $controller->$method($id);
		};		
		return $callback;		
	}
    
    public function buildController($site, $resource, SitesConfig $config)
    {
        // die("<p>Debug :: " . __FILE__ . "(" . __LINE__ . ") :: " . __FUNCTION__ . " :: message</p>");
        $modelClass = $config->getModelClass($site, $resource);
        die("<p>Debug :: " . __FILE__ . "(" . __LINE__ . ") :: " . __FUNCTION__ . " :: message</p>");
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
        
        die("<p>Debug :: " . __FILE__ . "(" . __LINE__ . ") :: " . __FUNCTION__ . " :: message</p>");
        $connection = $this->getSiteBasedConnection();
        if (!$connection) {
            throw new RouteException();
        }
        $modelInstance = new $modelClass($connection);			
        $controller = new $controllerClass($modelInstance);
        return $controller;
    }
    
    protected function getSiteBasedConnection()
    {
        $site = explode('/', \Request::path());
        $site = $site[0];
        // $site = in_array($site, $this->app['config']['app.core-resources']) ? 'default' : $site;
        return $this->app->make('mongo')->connection($site);
        
    }
    
    public function buildMethod($type)
    {
        $httpMethod = $this->app['request']->getMethod();
        $method = $this->getMethod($type, $httpMethod);
        return $method;                
    }
    
	public function getMethod($type, $httpMethod)
	{
		$type = strtolower($type);        
        if (!in_array($type, array('singular', 'plural'))) {
			throw new RouteException('Invalid plural/singular parameter: ' . $type);
		}
		return $this->methodMap[strtolower($httpMethod) . ucfirst($type)];
	}
    
    public function addCoreRoutes()
    {
        $coreResources = $this->app['config']['app.core-resources'];
        $connection = $this->app['mongo']->connection('default');
        foreach ($coreResources as $resource) {
            $this->addCoreRoute($resource, $connection);
        }
    }
    
    public function addCoreRoute($resource, $connection)
    {
        $creator = $this;
        
        $pluralUrl = $resource;
        $pluralCallback = function() use ($connection, $resource, $creator) {
            
            $modelClass = ucfirst($resource);
            $model = new $modelClass($connection);
            
            $controllerClass = ucfirst($resource) . 'Controller';
            $controller = new $controllerClass($model);
            
            $method = $creator->buildMethod('plural');
            
            return $controller->$method();
        };
        
        $singularUrl = $resource . '/{id}';
        $singularCallback = function($id) use ($connection, $resource, $creator) {
            
            $modelClass = ucfirst($resource);
            $model = new $modelClass($connection);
            
            $controllerClass = ucfirst($resource) . 'Controller';
            $controller = new $controllerClass($model);
            
            $method = $creator->buildMethod('singular');
            
            return $controller->$method($id);
        };
        
        $this->addRoutesToRouter($this->app['router'], 
                $singularUrl, $singularCallback, 
                $pluralUrl, $pluralCallback);
    }    
}
