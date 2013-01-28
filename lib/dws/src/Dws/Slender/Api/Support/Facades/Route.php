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
			$controller = 'App\\Controller\Site\\' . ucfirst($site) . '\\'
				. ucfirst($resource) . 'Controller';
		}
		
		$singularRoute	= static::buildSingularRoute($site, $resource);
		$pluralRoute	= static::buildPluralRoute($site, $resource);
		
		// Add GET routes
		static::$app['router']->get($singularRoute, $controller . '@' . 'httpGetSingular');
		static::$app['router']->get($pluralRoute, $controller . '@' . 'httpGetPlural');
		
		// Add PUT route
		static::$app['router']->put($pluralRoute, $controller . '@' . 'httpPutSingular');
				
		// Add DELETE route
		static::$app['router']->delete($pluralRoute, $controller . '@' . 'httpDeleteSingular');
		
		// Add OPTIONS routes
		static::$app['router']->match('options', $pluralRoute, $controller . '@' . 'httpOptionsPlural');
		static::$app['router']->match('options', $pluralRoute, $controller . '@' . 'httpOptionsSingular');
	}
	
	protected static function buildSingularRoute($site, $resource)
	{
		return $site . '/' . $resource . '/{id}';
	}
	
	protected static function buildPluralRoute($site, $resource)
	{
		return $site . '/' . $resource;
	}
	
	/**
	 * Add a new HTTP OPTIONS route to the collection.
	 *
	 * @param  string  $pattern
	 * @param  mixed   $action
	 * @return Illuminate\Routing\Route
	 */
	public static function options($pattern, $action)
	{
		static::$app['router']->match('options', $pattern, $action);
	}


}
