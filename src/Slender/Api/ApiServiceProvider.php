<?php namespace Slender\Api;

use Illuminate\Support\ServiceProvider;
use Slender\Api\Route\RouteCreator;

class ApiServiceProvider extends ServiceProvider {

	/**
	 * Indicates if loading of the provider is deferred.
	 *
	 * @var bool
	 */
	protected $defer = false;

	/**
	 * Bootstrap the application events.
	 *
	 * @return void
	 */
	public function boot()
	{
		
		$this->package('slender/api');
		include __DIR__.'/../../bootstrap/start.php';
		include __DIR__.'/../../app/start/global.php';
		
	}

	protected function registerRouteCreator()
	{

		$this->app['route-creator'] = $this->app->share(function($app)
		{


			return new RouteCreator($this->app, $config);

		});

	}

	/**
	 * Register the service provider.
	 *
	 * @return void
	 */
	public function register()
	{
		$this->registerRouteCreator();
	}

	/**
	 * Get the services provided by the provider.
	 *
	 * @return array
	 */
	public function provides()
	{
		return array();
	}

}