<?php

namespace Dws\Slender\Api\Route\SiteBasedResources;

use Illuminate\Foundation\Application;

/**
 * An object to create REST routes for site-based resources
 *
 * @author David Weinraub <david.weinraub@diamondwebservices.com>
 */
class Creator
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
		'postSingular' => 'insert',
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
	public function addRoutes(array $config)
	{
        $config = new Config($config);
        
		$router = $this->app['router'];

		$singularUrl = '{site}/{resource}/{id}';
		$singularCallback = $this->buildSingularCallback($config);
		
		$pluralUrl = '{site}/{resource}';
		$pluralCallback = $this->buildPluralCallback($config);
        
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
	
	protected function buildPluralUrl($site, $resource)
	{
		return $this->buildUrl($site, $resource, false);
	}
	
	protected function buildSingularUrl($site, $resource)
	{
		return $this->buildUrl($site, $resource, true);
	}
	
	protected function buildUrl($site, $resource, $isSingular)
	{
		$route = ($site ? $site . '/' : '') . $resource;
		if ($isSingular) {
			$route .= '/{id}';
		}
		return $route;	
	}
	
	protected function buildPluralCallback(Config $config)
	{
		$creator = $this;		
		$callback = function($site, $resource) use ($creator, $config) {
            $controller = $creator->buildController($site, $resource, $creator, $config);
            $method = $creator->buildMethod('plural');
            return $controller->$method();
		};		
		return $callback;		
	}
	
	protected function buildSingularCallback(Config $config)
	{
		$creator = $this;
		$callback = function($site, $resource, $id) use ($creator, $config) {
            $controller = $creator->buildController($site, $resource, $creator, $config);
            $method = $creator->buildMethod('singular');
			return $controller->$method($id);
		};		
		return $callback;		
	}
    
    public function buildController($site, $resource, $creator, $config)
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
        $connection = $creator->app['MongoSiteSingleton'];
        $modelInstance = new $modelClass($connection);			
        $controller = new $controllerClass($modelInstance);
        return $controller;
    }
    
    public function buildMethod($type)
    {
        $httpMethod = $this->app['request']->getMethod();
        $method = $this->getMethod($type, $httpMethod);
        return $method;                
    }
    
    public function confirmModelAndClass($modelClass, $controllerClass)
    {
        if (!$modelClass) {
            throw new RouteException(
                sprintf('Unable to create model',
                        $resource, $site));
        }

        if (!$controllerClass) {
            throw new RouteException(
                sprintf('Unable to resolve controller for resource %s and site %s',
                        $resource, $site));
        }
    }	
	
	public function getMethod($type, $httpMethod)
	{
		$type = strtolower($type);        
        if (!in_array($type, array('singular', 'plural'))) {
			throw new RouteException('Invalid plural/singular parameter: ' . $type);
		}
		return $this->methodMap[strtolower($httpMethod) . ucfirst($type)];
	}    
}
