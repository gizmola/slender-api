<?php

use Dws\Slender\Api\Route\RouteException;

use \Slender\API\Model\Users as UserModel;

/*
|--------------------------------------------------------------------------
| Register The Laravel Class Loader
|--------------------------------------------------------------------------
|
| In addition to using Composer, you may use the Laravel class loader to
| load your controllers and models. This is useful for keeping all of
| your classes in the "global" namespace without Composer updating.
|
|
| ClassLoader::addDirectories(array(
|
|    app_path().'/controllers',
|    app_path().'/models',
|
| ));
|
*/

/*
|--------------------------------------------------------------------------
| Application Error Logger
|--------------------------------------------------------------------------
|
| Here we will configure the error logger setup for the application which
| is built on top of the wonderful Monolog library. By default we will
| build a rotating log file setup which creates a new file each day.
|
*/

// Log::useDailyFiles(__DIR__.'/../storage/logs/log.txt');

/*
|--------------------------------------------------------------------------
| Application Error Handler
|--------------------------------------------------------------------------
|
| Here you may handle any errors that occur in your application, including
| logging them or displaying custom views for specific errors. You may
| even register several error handlers to handle different types of
| exceptions. If nothing is returned, the default error view is
| shown, which includes a detailed stack trace during debug.
|
*/

/**
 * 500 handler
 */
App::error(function(Exception $exception, $code = 500)
{

    $message = $exception->getMessage();

    if (App::environment() == 'local') {

        $message .= sprintf(' File: %s Line: %s Code: %s', $exception->getFile(), $exception->getLine(), $exception->getCode());
        
    }

    return Response::json(array(
        'messages' => array(
            $message,
        ),
    ), $code);

    //Log::error($exception);
});

App::error(function(RouteException $exception)
{
    // Log::error($exception);
    return Response::json(array(
		'messages' => array(
			$exception->getMessage(),
		),
	), 404);
});

App::missing(function(\Exception $exception)
{
    return Response::json(array(
		'messages' => array(
			'Resource not found',
		),
	), 404);
});


// Initialize the user-model and client-user in the container

App::singleton('user-model', function(){
    return new UserModel();
});
App::singleton('client-user', function(){
    return null;
});

/*
|--------------------------------------------------------------------------
| Require The Filters File
|--------------------------------------------------------------------------
|
| Next we will load the filters file for the application. This gives us
| a nice separate location to store our route and application filter
| definitions instead of putting them all in the main routes file.
|
*/

require __DIR__.'/../filters.php';

/*
* set the query parameters that should not be
* cast to a different data type and remain as
* strings
*/

$dontCast = ['zipcode','phone'];
ParamsHelper::setDontCast($dontCast);
