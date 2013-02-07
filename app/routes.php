<?php

use Dws\Slender\Api\Route\SiteBasedResources\RouteException;

App::error(function(RouteException $exception)
{
    return App::missing($exception);
});

/**
 * 500 handler
 */
App::error(function(Exception $exception)
{
    $message = $exception->getMessage() ?: 'Unknown error: code ' . $exception->getCode();
    return Response::json(array(
        'messages' => array(
            $message,
        ),
    ), 500);
});

/**
 * 404 handler
 */
App::missing(function($exception)
{
    return Response::json(array(
		'messages' => array(
			'Resource not found',
		),
	), 404);
});


App::singleton('MongoSiteSingleton', function(){
    // inspect Request, get site

	$site = explode("/", \Request::path());
	$site = $site[0];
	
	$site = in_array($site, array('users','roles')) ? 'default' : $site;

    return App::make('mongo')->connection($site);
});

App::singleton('MongoCommonSingleton', function(){
    return App::make('mongo')->connection('default');
});

Route::get('/', function(){
    return "OK";
});

/**
 * Add non-site dependent resource  
 */
Route::addRestResource('roles');
Route::addRestResource('users');
Route::addRestResource('pages');

// simple route with a view
// Route::get('sample-home', 'SampleHomeController@showWelcome');

// simple route to demonstate that we can load utilities from
// an outside namespace
//Route::get('help', function(){
//	Dws\Slender\Api\Controller\SomeHelper::help();
//});

/**
 * And the cool thing.
 *
 * Simple syntax to add all the standard routes we want in a single call:
 * 
 *	Route::addSiteRestResource('ai', 'news');
 *
 * This creates a bunch of routes:
 * 
 *	GET /ai/new
 *	GET /ai/news/:id
 *	PUT /ai/news/:id
 *	DELETE /ai/news/:id
 * 
 *	OPTIONS /ai/news
 *	OPTIONS /ai/news/:id
 * 
 * These routes are handled by
 * 
 *	app/controllers/site/ai/NewsController.php
 *		
 * which extends 
 *
 *	app/controllers/NewsController.php
 * 
 * which extends 
 * 
 *	app/controllers/NewsController.php
 *		
 * giving us the ability to establish default behaviors at the base and to override 
 * per resource-type (ex: news) and per site (ex: for ai)
 * 
 * To make this method work, I have created a new facade for Route and activated 
 * it in:
 * 
 *	app/config/app.php
 * 
 */
// Route::addSiteRestResource('ai', 'albums');
// Route::addSiteRestResource('ai', 'news');
// Route::addSiteRestResource('ai', 'pages');
// Route::addSiteRestResource('ai', 'photos');
// Route::addSiteRestResource('ai', 'videos');

/**
 * Config for the site-based-resources.
 * 
 * In principle, I expect that this will be stored in the cross-site db and then 
 * accessed via a model like SiteBasedResources. Then we can manage them via 
 * a bunch of site-independent routes, like we do with users and roles.
 * 
 * The upshot is that for each site, we simply list the resources we want to be 
 * available. We then construct routes - really only two routes, 
 * a singular and a plural one for each HTTP method we support - that, when matched 
 * instantiate the right models, and controllers and call the correct controller
 * method.
 * 
 * For example, a request for /ai/news would invoke the following pseudo-code:
 * 
 * if (news is enabled for ai){
 * 
 *    if (class_exists(App/Model/Site/Ai/News){
 *       use this model
 *    } else if (class_exists(News){
 *       use that model
 *    } else {
 *       404
 *    }
 * 
 *    if (class_exists(App/Controller/Site/Ai/News){
 *       use this controller
 *    } else if (class_exists(News){
 *       use that controller
 *    } else {
 *       404
 *    }
 * 
 *    // Inject connection into model
 *    // Inject model into controller
 *    // Call relevant method on controller
 *
 * } else {
 *    return 404
 * }
 * 
 * There is a lot of default handling implicit in here. But I expect that it 
 * will allow us to:
 * 
 * 1. Define base resource controllers and models
 * 2. Allow us to override on a per-site, per-resource basis
 * 3. Does not *require* that we create per-site class/model skeletons
 * 4. 
 */
$siteBasedResourceConfig = array(
    
    // ai has a lot of resources, for example
    'ai' => [
        'news',
        'photos',
        'albums',
        'videos',
        'pages',
    ],
    
    // txf only has news, for example
    'txf' => [
        'news',
    ],
);
App::make('site-based-resource-route-creator')->addRoutes($siteBasedResourceConfig);
