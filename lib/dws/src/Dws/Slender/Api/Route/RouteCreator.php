<?php

namespace Dws\Slender\Api\Route;

use Illuminate\Foundation\Application;
use LMongo\LMongoManager;
use Illuminate\Support\Facades\Request;

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
     * @var Auth method
     */
    protected $auth = 'auth';    
    
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
		'optionsPlural' => 'options',
	];
    
    /**
     *
     * @var \LMongoManager
     */
    protected $mongoManager;
	

    /**
     *
     * @var SitesConfig
     */
    protected $sitesConfig = null;

    /**
     *
     * @var array
     */
    protected $coreResources = [];

	/**
	 * Constructor
	 * 
	 * @param \Illuminate\Foundation\Application $app
	 */
    public function __construct(Application $app = null, LMongoManager $mongoManager = null)
    {
        if (!$app){
            $app = \App::instance();
        }
		$this->app = $app;
        if ($mongoManager){
            $this->setMongoManager($mongoManager);
        }
    }

	public function addSiteRoutes(array $config)
	{
        $this->sitesConfig = new SitesConfig($config);
		$singularUrl = '{site}/{resource}/{id}';
		$singularCallback = $this->buildSiteSingularCallback($this->sitesConfig);
		
		$pluralUrl = '{site}/{resource}';
		$pluralCallback = $this->buildSitePluralCallback($this->sitesConfig);
        
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
		return array('before' => $this->auth, $callback);		
	}
	
	public function buildSiteSingularCallback(SitesConfig $config)
	{
        $creator = $this;
		$callback = function($site, $resource, $id) use ($creator, $config) {
            $controller = $creator->buildSiteController($site, $resource, $config);
            $method = $creator->buildControllerMethod('singular');
			return $controller->$method($id);
		};		
		return array('before' => $this->auth, $callback);   
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
        //juni.samos@diamondwebservices.com
        //inject a class resolver for instatiating relations
        //from multiple namespaces
        $resolver = $this->app->make('class-resolver');
        $modelInstance->setResolver($resolver);
        
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
        $this->coreResources = $this->app['config']['app.core-resources'];
        $connection = $this->getMongoManager()->connection('default');
        foreach ($this->coreResources as $resource) {
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
            
            $modelClass = 'Slender\API\Model\\' . ucfirst($resource);            
            $model = new $modelClass($connection);
            //juni.samos@diamondwebservices.com
            //inject a class resolver for instatiating relations
            //from multiple namespaces
            $resolver = $this->app->make('class-resolver');
            $model->setResolver($resolver);
            
            $controllerClass = 'Slender\\API\\Controller\\' . ucfirst($resource) . 'Controller';
            $controller = new $controllerClass($model);
            
            $method = $creator->buildControllerMethod('plural');
            
            return $controller->$method();
        };
        
        $singularUrl = $resource . '/{id}';
        $singularCallback = function($id) use ($connection, $resource, $creator) {

            $modelClass = 'Slender\API\Model\\' . ucfirst($resource);
            $model = new $modelClass($connection);
            //juni.samos@diamondwebservices.com
            //inject a class resolver for instatiating relations
            //from multiple namespaces
            $resolver = $this->app->make('class-resolver');
            $model->setResolver($resolver);
            
            $controllerClass = 'Slender\API\Controller\\' . ucfirst($resource) . 'Controller';            
            $controller = new $controllerClass($model);
            
            $method = $creator->buildControllerMethod('singular');
            
            return $controller->$method($id);
        };
        
        $this->addRoutesToRouter($this->app['router'], 
                                    $singularUrl, 
                                    array('before' => $this->auth, $singularCallback), 
                                    $pluralUrl, 
                                    array('before' => $this->auth, $pluralCallback)
                                );
        
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

    /**
     * Returns Request Path
     * 
     * @param string $delimiter
     * @return string
     */
    public function getRequestPath($delimiter = null)
    {

        $return = [];

        // subject?
        if(array_key_exists(Request::segment(1), $this->sitesConfig->getConfig()))
        {
            $return[] = Request::segment(1);
            $site =  Request::segment(1);
            if(in_array(Request::segment(2), $this->sitesConfig->getConfig()[$site]))
            {
                $return[] = Request::segment(2);
            }
        }elseif(in_array(Request::segment(1), $this->coreResources)){
            $return[] = 'global';
            $return[] = Request::segment(1);
        }

        // to do what?                 
        switch (Request::getMethod()) {
            case 'GET':
            case 'OPTIONS':
                $return[] = 'read';
                break;
            case 'POST':
            case 'PUT':
                $return[] = 'write';
                break;   
            case 'DELETE':
                $return[] = 'delete';
                break;                         
        }

        if($delimiter){
            return implode($delimiter, $return);
        }
        return $return;
    }
}
