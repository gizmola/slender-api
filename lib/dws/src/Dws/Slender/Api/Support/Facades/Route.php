<?php

namespace Dws\Slender\Api\Support\Facades;

use Illuminate\Support\Facades\Route as LaravelFacadeRoute;

/**
 * A class to make custom rest routes in Laravel
 *
 * @author David Weinraub <david.weinraub@diamondwebservices.com>
 */
class Route extends LaravelFacadeRoute
{

	public static function addSiteRestResource($site, $resource, $controller = null)
	{
		if (null == $controller) {
			$controller = 'App\Controller\Site\\' . ucfirst($site) . '\\'
				. ucfirst($resource) . 'Controller';
		}
		
		$singularRoute	= static::buildRoute($site, $resource, true);
		$pluralRoute	= static::buildRoute($site, $resource, false);
		
		// Add GET routes
		static::$app['router']->get($singularRoute, $controller . '@view');
		static::$app['router']->get($pluralRoute, $controller . '@index');
		
		// Add PUT route
		static::$app['router']->put($singularRoute, $controller . '@update');
				
		// Add POST route
		static::$app['router']->post($pluralRoute, $controller . '@insert');
				
		// Add DELETE route
		static::$app['router']->delete($singularRoute, $controller . '@delete');
		
		// Add OPTIONS routes
		static::$app['router']->match('options', $pluralRoute, $controller . '@options');
	}
	
	protected static function buildRoute($site, $resource, $isSingular = false)
	{
		$route = $site . '/' . $resource;
		if ($isSingular) {
			$route .= '/{id}';
		}
		return $route;
	}
	
//	/**
//	 * Add a new HTTP OPTIONS route to the collection.
//	 *
//	 * @param  string  $pattern
//	 * @param  mixed   $action
//	 * @return Illuminate\Routing\Route
//	 */
//	public function options($pattern, $action)
//	{
//		static::$app['router']->match('options', $pattern, $action);
//	}

}
