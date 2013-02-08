<?php

namespace Dws\Slender\Api\Route;

use Illuminate\Support\ServiceProvider as BaseServiceProvider;

/**
 * A Laravel 4 service-provider for 
 *
 * @author David Weinraub <david.weinraub@diamondwebservices.com>
 */
class RouteServiceProvider extends BaseServiceProvider
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
			return new RouteCreator($app);
		});
	}

	/**
	 * Get the services provided by the provider.
	 *
	 * @return array
	 */
	public function provides()
	{
		return array('route-creator');
	}
}
