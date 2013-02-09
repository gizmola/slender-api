<?php

namespace Dws\Slender\Api\Resolver;

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
		$this->app['class-resolver'] = $this->app->share(function($app){
			return new ClassResolver();
		});
	}

	/**
	 * Get the services provided by the provider.
	 *
	 * @return array
	 */
	public function provides()
	{
		return array('class-resolver');
	}
}