<?php namespace Dws\Slender\Api\Resource;

use Illuminate\Support\ServiceProvider;

class ResourceServiceProvider extends ServiceProvider {

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
    }

    /**
     * Register the service provider.
     *
     * @return void
     */
    public function register()
    {
        $this->app['resource-writer'] = $this->app->share(function($app){
            $config = array_get($app['config'], 'resource-writer');
            $config['fallback-namespace'] = array_get($app['config'], 'app.fallbackNamespaces.resources');
            return new ResourceWriter($config);
        });

        $this->app['resource-namespace-manager'] = $this->app->share(function($app){
            $config = array_get($app['config'], 'resource-namespace');
            return new ResourceNamespaceManager($config);
        });

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