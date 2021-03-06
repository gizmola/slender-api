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
        $this->app['resource-resolver'] = $this->app->share(function($app){
            $resources = $app->make('config-manager')->getResourceConfig();
            $resourceResolver = new ResourceResolver($resources);
            $fallbackNamespace = $app['config']['app.fallbackNamespaces.resources'];
            $resourceResolver->setFallbackNamespace($fallbackNamespace);
            return $resourceResolver;
        });

        $this->app['permissions-resolver'] = $this->app->share(function($app){
            $resourceResolver = $app->make('resource-resolver');
            return new PermissionsResolver($resourceResolver);
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
            'resource-resolver',
            'permissions-resolver',
        );
	}
}