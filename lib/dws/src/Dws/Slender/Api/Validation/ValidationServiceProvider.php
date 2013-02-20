<?php

namespace Dws\Slender\Api\Validation;

use Illuminate\Validation\DatabasePresenceVerifier as DatabasePresenceVerifier;
use Illuminate\Support\ServiceProvider;


class ValidationServiceProvider extends ServiceProvider
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
		$this->registerPresenceVerifier();

		$this->app['validator'] = $this->app->share(function($app)
		{
			$validator = new Factory($app['translator']);

            // The validation presence verifier is responsible for determing the existence
			// of values in a given data collection, typically a relational database or
			// other persistent data stores. And it is used to check for uniqueness.
			if (isset($app['validation.presence']))
			{
				$validator->setPresenceVerifier($app['validation.presence']);
			}

			return $validator;
		});

        \Validator::extend('string', function($attribute, $value, $parameters){
            return (!is_array($value) && !is_object($value));
        });

        \Validator::extend('array', function($attribute, $value, $parameters){
            return is_array($value);
        });

        \Validator::extend('datetime', function($attribute, $value, $parameters){
            try {
                $d = new DateTime($value);  // let DateTime do the heavy lifting
                return true;
            } catch (\Exception $e) {
                return false;
            }
        });
	}

	/**
	 * Register the database presence verifier.
	 *
	 * @return void
	 */
	protected function registerPresenceVerifier()
	{
		$this->app['validation.presence'] = $this->app->share(function($app)
		{
			return new DatabasePresenceVerifier($app['db']);
		});
	}

	/**
	 * Get the services provided by the provider.
	 *
	 * @return array
	 */
	public function provides()
	{
		return array('validator', 'validation.presence');
	}

}