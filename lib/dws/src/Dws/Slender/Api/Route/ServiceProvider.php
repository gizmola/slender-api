<?php

namespace Dws\Slender\Api\Route;

use Illuminate\Support\ServiceProvider as BaseServiceProvider;

/**
 * A Laravel 4 service provider for Slender database connections
 *
 * @author David Weinraub <david.weinraub@diamondwebservices.com>
 */
class ServiceProvider extends BaseServiceProvider
{
	/**
	 * Indicates if loading of the provider is deferred.
	 *
	 * @var bool
	 */
	protected $defer = true;

	/**
	 * Register the service provider.
	 *
	 * @return void
	 */
	public function register()
	{
		$this->app['route-creator'] = $this->app->share(function($app)
		{
            $resourceResolver = $app->make('resource-resolver');
			$routeCreator = new RouteCreator($resourceResolver);
            return $routeCreator;
		});
	}

	/**
	 * Get the services provided by the provider.
	 *
	 * @return array
	 */
	public function provides()
	{
		return array(
            'route-creator',
        );
	}
}