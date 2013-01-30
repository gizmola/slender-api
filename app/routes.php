<?php

/**
 * 500 handler
 */
App::error(function($exception)
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


class Foo {}
class Bar {}

App::make('foo', function(){
	return new Foo();
});

App::singleton('bar', function(){
	return new Bar();
});

Route::get('foo', function(){
	$foo1 = App::make('foo');
	$foo2 = App::make('foo');
	$bar1 = App::make('bar');
	$bar2 = App::make('bar');
	var_dump($foo1, $foo2, $bar1, $bar2);
});

// simple system-responding route
Route::get('/', 'IndexController@index');
// Route::options('/', function(){ return 'OK OPTIONS'; });


// simple route with a view
Route::get('sample-home', 'SampleHomeController@showWelcome');

// simple route to demonstate that we can load utilities from
// an outside namespace
Route::get('help', function(){
	Dws\Slender\Api\Controller\SomeHelper::help();
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
Route::addSiteRestResource('ai', 'news');
Route::addSiteRestResource('ai', 'pages');
Route::addSiteRestResource('ai', 'videos');
