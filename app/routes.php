<?php

use Dws\Slender\Api\Controller\SomeHelper;

App::error(function($exception)
{
	die($exception);
});

App::missing(function($exception)
{
    return View::make('errors.missing');
});

// simple system-responding route
Route::get('/', function(){ die('OK'); });


// simple system-responding route
Route::options('/', function(){ die('OK OPTIONS'); });


// simple route with a view
Route::get('sample-home', 'SampleHomeController@showWelcome');

// simple route to demonstate that we can load utilities from
// an outside namespace
Route::get('help', function(){
	SomeHelper::help();
});

/**
 * And the cool thing.
 *
 * Simple syntax to add all the standard routes we want in a single call:
 * 
 *	Route::addSiteRestResource('ai', 'videos');
 *
 * This creates a bunch of routes:
 * 
 *	GET /ai/videos
 *	GET /ai/videos/:id
 *	PUT /ai/videos/:id
 *	DELETE /ai/videos/:id
 * 
 *	OPTIONS /ai/videos		(not yet implemented)
 *	OPTIONS /ai/videos/:id	(not yet implemented)
 * 
 * These routes are handled by
 * 
 *	app/controllers/site/ai/VideosController.php
 *		
 * which extends 
 *
 *	app/controllers/VideosController.php
 * 
 * which extends 
 * 
 *	app/controllers/BaseController.php
 *		
 * giving us the ability to establish default behaviors at the base and to override 
 * per resource-type (ex: videos) and per site (ex: for ai)
 * 
 * To make this method work, I have created a new facade for Route and activated 
 * it in:
 * 
 *	app/config/app.php
 * 
 */
Route::addSiteRestResource('ai', 'videos');
Route::addSiteRestResource('ai', 'pages');
