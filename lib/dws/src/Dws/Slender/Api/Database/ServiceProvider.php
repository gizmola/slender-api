<?php

namespace Dws\Slender\Api\Database;

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
		$this->app['MongoSiteSingleton'] = $this->app->share(function($app){
            // Inspect Request, get site
            $site = explode('/', \Request::path());
            $site = $site[0];
            $site = in_array($site, array('users','roles')) ? 'default' : $site;
            return $app->make('mongo')->connection($site);
		});
        
		$this->app['MongoCommonSingleton'] = $this->app->share(function($app){
            return $app->make('mongo')->connection('default');
        });
	}

	/**
	 * Get the services provided by the provider.
	 *
	 * @return array
	 */
	public function provides()
	{
		return array('MongoSiteSingleton', 'MongoCommonSingleton');
	}
}
