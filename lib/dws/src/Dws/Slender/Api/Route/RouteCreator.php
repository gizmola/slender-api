<?php

namespace Dws\Slender\Api\Route;

use \App;
use Illuminate\Routing\Router;
use Dws\Slender\Api\Resolver\ResourceResolver;

/**
 * An object to create REST routes for site-based resources
 *
 * @author David Weinraub <david.weinraub@diamondwebservices.com>
 */
class RouteCreator
{
    /**
     * Resource resolver
     *
     * @var ResourceResolver
     */
    protected $resourceResolver;

    /**
     * The router to which we add the routes
     * 
     * @var Router
     */
    protected $router;

    /**
     * @var Name of the filter to use
     */
    protected $auth = 'auth-common-permissions';

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
     * Constructor
     * 
     * @param \Dws\Slender\Api\Resolver\ResourceResolver $resourceResolver
     */
    public function __construct(ResourceResolver $resourceResolver)
    {
        $this->resourceResolver = $resourceResolver;
    }

    /**
     * Add all configured routes to the router. Primary entry point.
     *
     * @return void
     */
    public function addRoutes()
    {
        $this->addCoreRoutes();
        $this->addSiteRoutes();
    }

    /**
     * Add site-specific routes
     * 
     * @return void
     */
	protected function addSiteRoutes()
	{
        $creator = $this;
        $resolver = $this->resourceResolver;

		$singularUrl = '{site}/{resource}/{id}';
		$singularCallback = function($site, $resource, $id) use ($creator, $resolver) {
            $controller = $resolver->buildControllerInstance($resource, $site);
            $method = $creator->buildControllerMethod('singular');
			return $controller->$method($id);
		};

		$pluralUrl = '{site}/{resource}';
		$pluralCallback = function($site, $resource) use ($creator, $resolver) {
            $controller = $resolver->buildControllerInstance($resource, $site);
            $method = $creator->buildControllerMethod('plural');
			return $controller->$method();
		};

        $this->addRoutesToRouter(
                $singularUrl, 
                array('before' => $this->auth, $singularCallback),
                $pluralUrl, 
                array('before' => $this->auth, $pluralCallback)
        );
	}

    /**
     * Utility method to add five method routes (GET, POSt, PUT, DELETE, OPTIONS)
     * routes to the router with a given set of singular and plural urls and callbacks.
     *
     * @param type $router
     * @param type $singularUrl
     * @param type $singularCallback
     * @param type $pluralUrl
     * @param type $pluralCallback
     */
    protected function addRoutesToRouter($singularUrl, $singularCallback, $pluralUrl, $pluralCallback)
    {
        $router = $this->getRouter();
        
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

    /**
     *
     * @param string $type Values: 'singular' or 'plural'
     * @return string
     */
    protected function buildControllerMethod($type)
    {
        $httpMethod = App::make('request')->getMethod();
        // $httpMethod = $this->getApp()['request']->getMethod();
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
	protected function getMappedMethod($type, $httpMethod)
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
    protected function addCoreRoutes()
    {
        $this->coreResources = $this->resourceResolver->getCoreResourceKeys();
        foreach ($this->coreResources as $resource) {
            $this->addCoreRoute($resource);
        }
    }

    /**
     * Add a single core route with the given resource name
     *
     * @param string $resource
     * @param LMongo\MongoManager $connection
     * @return Creator
     */
    protected function addCoreRoute($resource)
    {
        $creator = $this;
        $resolver = $this->resourceResolver;
        $pluralUrl = $resource;
        $pluralCallback = function() use ($resource, $creator, $resolver) {
            $controller = $resolver->buildControllerInstance($resource, null);
            $method = $creator->buildControllerMethod('plural');
            return $controller->$method();
        };

        $singularUrl = $resource . '/{id}';
        $singularCallback = function($id) use ($resource, $creator, $resolver) {
            $controller = $resolver->buildControllerInstance($resource, null);
            $method = $creator->buildControllerMethod('singular');
            return $controller->$method($id);
        };

        $this->addRoutesToRouter(
            $singularUrl,
            array('before' => $this->auth, $singularCallback),
            $pluralUrl,
            array('before' => $this->auth, $pluralCallback)
        );

        return $this;
    }

    /**
     * Get the router
     *
     * @return Router
     */
    public function getRouter()
    {
        if (null === $this->router) {
            $this->router = App::make('router');
        }
        return $this->router;
    }


    /**
     * Set the router
     * 
     * @param \Illuminate\Routing\Router $router
     * @return \Dws\Slender\Api\Route\RouteCreator
     */
    public function setRouter(Router $router)
    {
        $this->router = $router;
        return $this;
    }
}
