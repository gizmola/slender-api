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
Route::get('/', function(){ return 'OK'; });


// simple system-responding route
Route::options('/', function(){ return 'OK OPTIONS'; });


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
 *	Route::addSiteRestResource('ai', 'news');
 *
 * This creates a bunch of routes:
 * 
 *	GET /ai/new
 *	GET /ai/news/:id
 *	PUT /ai/news/:id
 *	DELETE /ai/news/:id
 * 
 *	OPTIONS /ai/news		(not yet implemented)
 *	OPTIONS /ai/news/:id	(not yet implemented)
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
Route::addSiteRestResource('ai', 'news');
Route::addSiteRestResource('ai', 'pages');
