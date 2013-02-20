<?php

namespace Dws\Slender\Api\Route;

use Illuminate\Foundation\Application;
use Illuminate\Support\Facades\Request;
use LMongo\LMongoManager;
use Dws\Slender\Api\Resolver\ClassResolver;
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
	 * @var Application
	 */
	protected $app;

    /**
     * A Mongo manager
     *
     * @var \LMongoManager
     */
    protected $mongoManager;

    /**
     * @var Name of the filter to use
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
	 * Constructor
	 *
	 * @param \Illuminate\Foundation\Application $app
	 */
    public function __construct(ResourceResolver $resourceResolver, Application $app = null, LMongoManager $mongoManager = null)
    {
        $this->resourceResolver = $resourceResolver;
        if ($app){
            $this->setApp($app);
        }
        if ($mongoManager){
            $this->setMongoManager($mongoManager);
        }
    }

    public function addRoutes()
    {
        $this->addCoreRoutes();
        $this->addSiteRoutes();
    }

	protected function addSiteRoutes()
	{
		$singularUrl = '{site}/{resource}/{id}';
		$singularCallback = $this->buildSiteSingularCallback();

		$pluralUrl = '{site}/{resource}';
		$pluralCallback = $this->buildSitePluralCallback();

        $this->addRoutesToRouter($this->getApp()['router'],
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

	protected function buildSitePluralCallback()
	{
        $creator = $this;
		$callback = function($site, $resource) use ($creator) {
            $controller = $creator->buildSiteController($site, $resource);
            $method = $creator->buildControllerMethod('plural');
            return $controller->$method();
		};
		return array('before' => $this->auth, $callback);
	}

	public function buildSiteSingularCallback()
	{
        $creator = $this;
		$callback = function($site, $resource, $id) use ($creator) {
            $controller = $creator->buildSiteController($site, $resource);
            $method = $creator->buildControllerMethod('singular');
			return $controller->$method($id);
		};
		return array('before' => $this->auth, $callback);
	}

    public function buildSiteController($site, $resource)
    {
        $modelClass = $this->resourceResolver->getResourceModelClassForSite($resource, $site);
        if (!$modelClass) {
            $msg = sprintf('Unable to resolve model for resource %s and site %s',
                        $resource, $site);
            throw new RouteException($msg);
        }

        $controllerClass = $this->resourceResolver->getResourceControllerClassForSite($resource, $site);
        if (!$controllerClass) {
            $msg = sprintf('Unable to resolve controller for resource %s and site %s',
                        $resource, $site);
            throw new RouteException($msg);
        }

        $connection = $this->getMongoManager()->connection($site);
        if (!$connection) {
            $msg = sprintf('No connection configured for resource %s and site %s',
                        $resource, $site);
            throw new RouteException($msg);
        }

        $modelInstance = new $modelClass($connection);
        $modelInstance->setRelations($this->resourceResolver->buildModelRelations($resource, $site));
        $modelInstance->setSite($site);
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
        $httpMethod = $this->getApp()['request']->getMethod();
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
            $model->setRelations($creator->resourceResolver->buildModelRelations($resource, null));
            $model->setSite(null);

            $controllerClass = 'Slender\\API\\Controller\\' . ucfirst($resource) . 'Controller';
            $controller = new $controllerClass($model);

            $method = $creator->buildControllerMethod('plural');

            return $controller->$method();
        };

        $singularUrl = $resource . '/{id}';
        $singularCallback = function($id) use ($connection, $resource, $creator) {

            $modelClass = 'Slender\API\Model\\' . ucfirst($resource);
            $model = new $modelClass($connection);
            $model->setRelations($creator->resourceResolver->buildModelRelations($resource, null));

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
     * @return Application
     */
    public function getApp()
    {
        if (!$this->app) {
            $this->app = \App::instance();
        }
        return $this->app;
    }

    /**
     * @param \Illuminate\Foundation\Application $app
     * @return \Dws\Slender\Api\Route\RouteCreator
     */
    public function setApp(Application $app)
    {
        $this->app = $app;
        return $this;
    }
}
